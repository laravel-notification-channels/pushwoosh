<?php

namespace NotificationChannels\Pushwoosh\Events;

class UnknownDevices
{
    /**
     * The referenced devices.
     *
     * @var string[]
     */
    public $devices;

    /**
     * The message ID.
     *
     * @var string
     */
    public $message;

    /**
     * Create a new event instance.
     *
     * @param string $message
     * @param array $devices
     * @return void
     */
    public function __construct(string $message, array $devices)
    {
        $this->devices = $devices;
        $this->message = $message;
    }
}
