<?php

namespace NotificationChannels\Pushwoosh;

use Illuminate\Support\Collection;

class PushwooshPendingMessage implements \JsonSerializable
{
    protected $client;
    protected $messages;
    protected $recipients;

    /**
     * Create a new pending message.
     *
     * @param \NotificationChannels\Pushwoosh\Pushwoosh $client
     * @return void
     */
    public function __construct(Pushwoosh $client)
    {
        $this->client = $client;
        $this->messages = new Collection();
        $this->recipients = new Collection();
    }

    /**
     * Send the message.
     *
     * @return void
     */
    public function __destruct()
    {
        if ($this->messages->isEmpty()) {
            return;
        }

        $this->client->createMessage($this);
    }

    /**
     * Convert the queued messages to something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'application' => $this->client->getApplicationCode(),
            'auth' => $this->client->getApiToken(),
            'notifications' => $this->messages->map(function (PushwooshMessage $message) {
                return $this->stamp($message);
            }),
        ];
    }

    /**
     * Queue the message.
     *
     * @param \NotificationChannels\Pushwoosh\PushwooshMessage $message
     * @return $this
     */
    public function queue(PushwooshMessage $message)
    {
        $this->messages->push($message);

        return $this;
    }

    /**
     * Stamp the message before sending it.
     *
     * @param \NotificationChannels\Pushwoosh\PushwooshMessage $message
     * @return array
     */
    protected function stamp(PushwooshMessage $message)
    {
        $recipients = $this->recipients->reduce(function (array $stamps, PushwooshRecipient $recipient) {
            return array_merge($stamps, $recipient->jsonSerialize());
        }, []);

        return array_merge($message->jsonSerialize(), $recipients);
    }

    /**
     * Set the recipient(s) for the queued messages.
     *
     * @param \NotificationChannels\Pushwoosh\PushwooshRecipient $recipient
     * @return $this
     */
    public function to(PushwooshRecipient $recipient)
    {
        $this->recipients->push($recipient);

        return $this;
    }

    /**
     * Tell the message it was sent.
     *
     * @return void
     */
    public function wasSent()
    {
        $this->messages = new Collection();
    }
}
