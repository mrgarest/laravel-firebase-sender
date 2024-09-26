<?php

namespace MrGarest\FirebaseSender\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FirebaseSenderLog extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $casts = [
        'high_priority' => 'boolean',
    ];

    /**
     * Checks for a record with a certain value.
     *
     * @param string $value
     * @param string|null $to The address to which the notification was sent.
     * @param string|null $type Type of notification sent (token or topic).
     * @param string|null $projectID
     * 
     * @return bool
     */
    public static function isValue(string $value, string|null $to = null, string|null $type = null, string|null $projectID = null): bool
    {
        $model = self::where('value', $value);
        if ($type != null) $model->where('type', $type);
        if ($to != null) $model->where('to', $to);
        if ($projectID != null) $model->where('project_id', $projectID);
        return $model->exists();
    }

    /**
     * Checks for a record with a specific value for a specific time range.
     *
     * @param string $datetime
     * @param string $value
     * @param string|null $to The address to which the notification was sent.
     * @param string|null $type Type of notification sent (token or topic).
     * @param string|null $projectID
     * 
     * @return bool Returns true if the value exists and `sent_at > $datetime`.
     */
    public static function isValueByTimeRange(string $datetime, string $value, string|null $to = null, string|null $type = null, string|null $projectID = null): bool
    {
        $model =  self::where('value', $value)
            ->where('sent_at', '>', $datetime);

        if ($type != null) $model->where('type', $type);
        if ($to !== null) $model->where('to', $to);
        if ($projectID != null) $model->where('project_id', $projectID);

        return $model->exists();
    }

    /**
     * Checks for a record with a certain recipient address for a certain period of time.
     *
     * @param string $datetime
     * @param string $to The address to which the notification was sent.
     * @param string|null $type Type of notification sent (token or topic).
     * @param string|null $projectID
     * 
     * @return bool Returns true if the recipient's address exists and `ent_at > $datetime`.
     */
    public static function isToByTimeRange(string $datetime, string $to, string|null $type = null, string|null $projectID = null): bool
    {
        $model =  self::where('to', $to)
            ->where('sent_at', '>', $datetime);

        if ($type != null) $model->where('type', $type);
        if ($projectID != null) $model->where('project_id', $projectID);

        return $model->exists();
    }
}
