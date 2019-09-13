<?php

namespace NotificationChannels\Pushwoosh;

use Illuminate\Support\Collection;
use RuntimeException;

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
     * @return \NotificationChannels\Pushwoosh\PushwooshException
     */
    public static function apiError($payload, $previous = null)
    {
        return new static(sprintf('Pushwoosh API error: %s', $payload->status_message), 0, $previous);
    }

    /**
     * Create a new exception for failed transmission.
     *
     * @param \Throwable|null $previous
     * @return \NotificationChannels\Pushwoosh\PushwooshException
     */
    public static function failedTransmission($previous = null)
    {
        return new static('Failed to create message(s)', 0, $previous);
    }

    /**
     * Create a new exception for unknown devices.
     *
     * @param object $payload
     * @param \Throwable|null $previous
     * @return \NotificationChannels\Pushwoosh\PushwooshException
     */
    public static function unknownDevices($payload, $previous = null)
    {
        $devices = Collection::make($payload->response->UnknownDevices)->reduce(function ($carry, $devices) {
            return array_merge((array)$carry, $devices);
        }, []);

        return new static(sprintf('Unknown device(s) mentioned: %s', implode(', ', $devices)), 0, $previous);
    }
}
