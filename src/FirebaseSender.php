<?php

namespace MrGarest\FirebaseSender;

use MrGarest\FirebaseSender\Models\FirebaseSenderLog;
use Google\Auth\CredentialsLoader;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use MrGarest\FirebaseSender\Exceptions as Ex;

class FirebaseSender
{
    private $serviceAccount = null;
    private array $to = ['type' => null, 'address' => null];
    private $isHighPriority = false;
    private $android = null;
    private $apns = null;
    private $notification = null;
    private $responseLog = null;
    private array $databaseLog = ['enabled' => false, 'value' => null];

    /**
     * Constructor of the class that initializes an object for interacting with Firebase Sender.
     *
     * @param string $serviceAccountName Name from the `service_accounts` array located in the `config/firebase-sender.php` file.
     */
    public function __construct(string $serviceAccountName)
    {
        $this->serviceAccount = config('firebase-sender.service_accounts.' . $serviceAccountName);
    }

    /**
     * Sets a high priority for the notification.
     */
    public function setHighPriority()
    {
        $this->isHighPriority = true;
        $this->android['priority'] = 'high';
        $this->apns['headers']['apns-priority'] = '10';
    }

    /**
     * Sets the time to live (TTL) for the notification.
     * 
     * @param int $seconds Time duration, in seconds.
     */
    public function setTimeToLive(int $seconds)
    {
        $this->android['ttl'] = $seconds . 's';
    }

    /**
     * Sets the token of a specific device to which the notification will be sent.
     *
     * @param string $token
     *
     * @throws Ex\SimultaneousUseException
     */
    public function setTokenDevices(string $token)
    {
        if ($this->to['type'] != null) throw new Ex\SimultaneousUseException();
        $this->to = [
            'type' => 'token',
            'address' => $token,
        ];
    }

    /**
     * Sets the topic for sending a group notification.
     *
     * @param string $topic
     *
     * @throws Ex\SimultaneousUseException
     */
    public function setTopic(string $topic)
    {
        if ($this->to['type'] != null) throw new Ex\SimultaneousUseException();
        $this->to = [
            'type' => 'topic',
            'address' => $topic,
        ];
    }

    /**
     * Sets the title of the notification.
     *
     * @param string $str
     */
    public function setTitle(string $str)
    {
        $this->notification['title'] = $str;
    }

    /**
     * Sets the localization key for the notification title.
     *
     * @param string $key Localization key.
     * @param array|null $args Array of arguments (optional).
     */
    public function setTitleLocKey(string $key, array|null $args = null)
    {
        $this->android['notification']['title_loc_key'] = $key;
        $this->apns['payload']['aps']['alert']['title-loc-key'] = $key;
        if ($args != null) {
            foreach ($args as $value) {
                $str = strval($value);
                $this->android['notification']['title_loc_args'][] = $str;
                $this->apns['payload']['aps']['alert']['title-loc-args'][] = $str;
            }
        }
    }

    /**
     * Sets the body of the notification.
     *
     * @param string $str
     */
    public function setBody(string $str)
    {
        $this->notification['body'] = $str;
    }

    /**
     * Sets the localization key for the notification body.
     *
     * @param string $key Localization key.
     * @param array|null $args Array of arguments (optional).
     */
    public function setBodyLocKey(string $key, array|null $args = null)
    {
        $this->android['notification']['body_loc_key'] = $key;
        $this->apns['payload']['aps']['alert']['loc-key'] = $key;
        if ($args != null) {
            foreach ($args as $value) {
                $str = strval($value);
                $this->android['notification']['body_loc_args'][] = $str;
                $this->apns['payload']['aps']['alert']['loc-args'][] = $str;
            }
        }
    }

    /**
     * Sets a link to an image.
     *
     * @param string|null $url
     */
    public function setImage(string|null $url)
    {
        $this->notification['image'] = $url;
    }

    /**
     * Sets the permission to write the log to the database after sending a notification.
     * For this method, you need to obtain the migration using `php artisan vendor:publish --tag=firebase-sender-migrations`, and then execute the migration itself with `php artisan migrate`.
     * 
     * @param string|null $value The value to be added when writing to the database (optional).
     */
    public function setDatabaseLog(int|float|string|null $value = null)
    {
        $this->databaseLog = ['enabled' => true, 'value' => strval($value)];
    }

    /**
     * Sends notifications.
     *
     * @return bool `true` if the push notification was successfully sent, `false` otherwise.
     */
    public function send(): bool
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json; UTF-8',
            'Authorization' => $this->makeBearerAuthorization(),
        ])->post("https://fcm.googleapis.com/v1/projects/{$this->serviceAccount['project_id']}/messages:send", [
            'message' => $this->makeMessages()
        ]);

        if (!$response->successful()) return false;
        $data = $response->json();

        if (!isset($data['name'])) return false;

        preg_match('/projects\/(.+?)\/messages\/(.*)/', $data['name'], $matches);

        $this->responseLog = [
            'message_id' => isset($matches[2]) ? $matches[2] : null,
            'project_id' => isset($matches[1]) ? $matches[1] : null,
        ];

        $this->databaseLog($this->responseLog['message_id'], $this->responseLog['project_id']);

        return true;
    }

    /**
     * Creating a message body for a notification.
     *
     * @return array
     */
    protected function makeMessages(): array
    {
        $message = [
            'notification' => $this->notification,
            'android' => $this->android,
            'apns' => $this->apns,
        ];
        $message[$this->to['type']] = $this->to['address'];

        return $message;
    }

    /**
     * Creating an OAuth2 access token for authorization.
     *
     * @return string
     * 
     * @throws Ex\AccessTokenMissingException
     */
    protected function makeBearerAuthorization(): string
    {
        $credentials = CredentialsLoader::makeCredentials('https://www.googleapis.com/auth/firebase.messaging', $this->serviceAccount);
        if (!isset($credentials->fetchAuthToken()['access_token'])) throw new Ex\AccessTokenMissingException();

        return 'Bearer ' . $credentials->fetchAuthToken()['access_token'];
    }

    /**
     * Writes the log to the database.
     *
     * @return array
     */
    protected function databaseLog($messageID, $projectID)
    {
        if ($messageID == null || $projectID == null || $this->databaseLog['enabled'] == false) return;

        $message[$this->to['type']] = $this->to['address'];
        $model = new FirebaseSenderLog();
        $model->message_id = $messageID;
        $model->project_id = $projectID;
        $model->high_priority = $this->isHighPriority;
        $model->type = $this->to['type'];
        $model->to = $this->to['address'];
        $model->value = $this->databaseLog['value'];
        $model->sent_at = Carbon::now();
        $model->save();
    }
}
