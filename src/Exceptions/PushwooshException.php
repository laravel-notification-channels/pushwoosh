<?php

namespace NotificationChannels\Pushwoosh\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Exception thrown following communication failure with the Pushwoosh API.
 */
class PushwooshException extends RuntimeException
{
    /**
     * Create a new exception for an API error.
     *
     * @param object $payload
     * @param \Throwable|null $previous
     * @return \NotificationChannels\Pushwoosh\Exceptions\PushwooshException
     */
    public static function apiError($payload, Throwable $previous = null)
    {
        return new static(sprintf('Pushwoosh API error: %s', $payload->status_message), 0, $previous);
    }

    /**
     * Create a new exception for failed transmission.
     *
     * @param \Throwable|null $previous
     * @return \NotificationChannels\Pushwoosh\Exceptions\PushwooshException
     */
    public static function failedTransmission(Throwable $previous = null)
    {
        return new static('Failed to create message(s)', 0, $previous);
    }
}
