<?php

namespace Garest\FirebaseSender\Jobs;

use Garest\FirebaseSender\Events\FirebaseMessageFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Garest\FirebaseSender\FirebaseSender;
use Garest\FirebaseSender\Models\FirebaseSenderLog;
use Garest\FirebaseSender\Utils;

class FirebaseSenderJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of seconds during which a task can run before timing out.
     *
     * @var int
     */
    public $timeout;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public bool $logsEnabled,
        public string $serviceAccount,
        public array $messages,
        public array|null $ulids,
    ) {
        $this->timeout = config('firebase-sender.job.send_timeout', 600);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $firebaseSender = new FirebaseSender($this->serviceAccount);
        $firebaseSender->logEnabled(false);
        $firebaseSender->setMessages($this->messages);
        $results = $firebaseSender->send();

        $updateData = [];
        foreach ($results->messages as $index => $message) {
            $ulid = $this->ulids[$index] ?? null;

            // If the message failed, fire the FirebaseMessageFailed event with the error details
            if (!$message->success) {
                event(new FirebaseMessageFailed(
                    serviceAccount: $this->serviceAccount,
                    address: $message->address,
                    target: $message->target,
                    code: $message->error->code,
                    status: $message->error->status,
                    message: $message->error->message
                ));
            }

            // If the log is disabled or ulid is not found, skip the log.
            if (!$this->logsEnabled || !$ulid) {
                continue;
            }

            $updateData[] = [
                'ulid' => $ulid,
                'service_account' => $this->serviceAccount,
                'message_id' => $message->success ? $message->messageId : null,
                'target' => $message->target,
                'to' => $message->address,
                'sent_at' => $message->success ? $message->datetime : null,
                'failed_at' => !$message->success ? $message->datetime : null,
                'exception' => Utils::messageToException($message)
            ];
        }

        if (!empty($updateData)) {
            collect($updateData)->chunk(500)->each(fn($chunk) => FirebaseSenderLog::upsert($chunk->toArray(), ['ulid'], ['message_id', 'sent_at', 'failed_at', 'exception']));
        }
    }
}
