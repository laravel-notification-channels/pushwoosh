<?php

namespace NotificationChannels\Pushwoosh\Concerns;

use Illuminate\Support\Str;
use Throwable;

trait DetectsPushwooshErrors
{
    /**
     * Determine if the given exception was caused by a communication error with Pushwoosh.
     *
     * @param \Throwable $e
     * @return bool
     */
    public function causedByPushwooshServerError(Throwable $e): bool
    {
        return Str::contains($e->getMessage(), [
            '502 Bad Gateway',
            '504 Gateway Time-out',
        ]);
    }
}
