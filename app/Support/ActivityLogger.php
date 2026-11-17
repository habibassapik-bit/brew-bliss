<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

final class ActivityLogger
{
    /**
     * Catat aktivitas penting admin ke tabel activity_logs.
     *
     * @param  array<string, mixed>  $properties
     */
    public static function log(string $action, ?Model $subject = null, array $properties = []): ?ActivityLog
    {
        if (auth()->id() === null) {
            return null;
        }

        return ActivityLog::query()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject !== null ? $subject->getMorphClass() : null,
            'subject_id' => $subject?->getKey(),
            'properties' => $properties !== [] ? $properties : null,
        ]);
    }
}
