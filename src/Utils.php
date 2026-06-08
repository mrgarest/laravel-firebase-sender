<?php

namespace Garest\FirebaseSender;

use Garest\FirebaseSender\DTO\MessageResult;

class Utils
{
    /**
     * Deletes all elements of the array whose value is null.
     *
     * @param array $args
     * 
     * @return array|null
     */
    public static function nullFilter(array $args): array|null
    {
        $filtered = [];

        foreach ($args as $key => $value) {
            if (is_array($value)) {
                $value = self::nullFilter($value);
            }

            if ($value !== null && (!is_array($value) || !empty($value))) {
                $filtered[$key] = $value;
            }
        }
        return empty($filtered) ? null : $filtered;
    }

    /**
     * Allows to get the recipient's address and the target of the message.
     *
     * @param array $message
     * 
     * @return array|null
     */
    public static function getRecipient(array $message): ?array
    {
        $key = array_key_first(array_intersect_key($message, array_flip([
            Target::TOKEN,
            Target::TOPIC,
            Target::CONDITION
        ])));

        return $key !== null ? [
            'target' => $key,
            'address' => $message[$key]
        ] : null;
    }

    /**
     * Converts message errors into a json string for logging.
     * 
     * @param MessageResult $message
     * 
     * @return string|null
     */
    public static function messageToException(MessageResult $message): ?string
    {
        if ($message->success) return null;
        
        return json_encode(
            [
                'code' => $message->error->code,
                'status' => $message->error->status,
                'message' => $message->error->message
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }
}
