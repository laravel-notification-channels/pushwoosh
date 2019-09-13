<?php

namespace NotificationChannels\Pushwoosh;

use Illuminate\Notifications\Notification;

class PushwooshChannel
{
    protected $pushwoosh;

    /**
     * Create a new Pushwoosh notification channel.
     *
     * @param \NotificationChannels\Pushwoosh\Pushwoosh $pushwoosh
     * @return void
     */
    public function __construct(Pushwoosh $pushwoosh)
    {
        $this->pushwoosh = $pushwoosh;
    }

    /**
     * Parse the message.
     *
     * @param mixed $message
     * @return \NotificationChannels\Pushwoosh\PushwooshMessage
     */
    protected function parseMessage($message)
    {
        return new PushwooshMessage((string)$message);
    }

    /**
     * Parse the recipient(s).
     *
     * @param mixed $recipients
     * @return \NotificationChannels\Pushwoosh\PushwooshRecipient
     */
    protected function parseRecipients($recipients)
    {
        return (new PushwooshRecipient)
            ->device(is_array($recipients) ? $recipients : func_get_args());
    }

    /**
     * Send the given notification.
     *
     * @param \Illuminate\Notifications\RoutesNotifications $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $recipients = $notifiable->routeNotificationFor('pushwoosh', $notification);

        /** @noinspection PhpUndefinedMethodInspection */
        if (!$recipients || !$message = $notification->toPushwoosh($notifiable)) {
            return;
        }

        if (!$message instanceof PushwooshMessage) {
            $message = $this->parseMessage($message);
        }

        if (!$recipients instanceof PushwooshRecipient) {
            $recipients = $this->parseRecipients($recipients);
        }

        $this->pushwoosh->send($message)->to($recipients);
    }
}
