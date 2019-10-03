<?php

namespace NotificationChannels\Pushwoosh\Exceptions;

use Illuminate\Support\Arr;
use Throwable;

class UnknownDeviceException extends PushwooshException
{
    protected $devices;

    /**
     * Create a new unknown device exception.
     *
     * @param mixed $devices
     * @param int $code
     * @param \Throwable|null $previous
     * @return void
     */
    public function __construct($devices, $code = 0, Throwable $previous = null)
    {
        $this->devices = (array)$devices;

        parent::__construct(
            sprintf('Unknown device(s) referenced: %s', implode(', ', Arr::flatten($this->devices))),
            $code,
            $previous
        );
    }

    /**
     * Get the unknown devices per message.
     *
     * @return string[][]
     */
    public function getDevices()
    {
        return $this->devices;
    }
}
