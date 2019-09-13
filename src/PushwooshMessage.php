<?php

namespace NotificationChannels\Pushwoosh;

use DateTimeInterface;
use DateTimeZone;
use Illuminate\Notifications\Notification;
use JsonSerializable;
use stdClass;

class PushwooshMessage implements JsonSerializable
{
    protected $campaign;
    protected $content;
    protected $identifier;
    protected $preset;
    protected $recipientTimezone;
    protected $shortenUrl;
    protected $timezone;
    protected $throughput;
    protected $url;
    protected $when;

    /**
     * Create a new push message.
     *
     * @param string $content
     * @return void
     */
    public function __construct($content = '')
    {
        $this->content = $content;
        $this->recipientTimezone = false;
        $this->when = 'now';
    }

    /**
     * Associate the message to the given notification.
     *
     * @param \Illuminate\Notifications\Notification $notification
     * @return $this
     */
    public function associate(Notification $notification)
    {
        if (!$this->identifier) {
            $this->identifier = $notification->id;
        }

        return $this;
    }

    /**
     * Set the Pushwoosh campaign code.
     *
     * @param string $campaign
     * @return $this
     */
    public function campaign($campaign)
    {
        $this->campaign = $campaign;

        return $this;
    }

    /**
     * Set the message content.
     *
     * @param string $content
     * @param string|null $language
     * @return $this
     */
    public function content($content, $language = null)
    {
        if ($language) {
            if (!is_array($this->content)) {
                $this->content = [];
            }

            $this->content[$language] = $content;
        } else {
            $this->content = $content;
        }

        return $this;
    }

    /**
     * Set the delivery moment.
     *
     * @param \DateTimeInterface|string $when
     * @param \DateTimeZone|string|null $timezone
     * @return $this
     */
    public function deliverAt($when, $timezone = null)
    {
        if ($when instanceof DateTimeInterface) {
            $timezone = $when->getTimezone();
            $when = $when->format('Y-m-d H:i');
        }

        if ($timezone instanceof DateTimeZone) {
            $timezone = $timezone->getName();
        }

        $this->timezone = $timezone;
        $this->when = $when;

        return $this;
    }

    /**
     * Set the message identifier.
     *
     * @param string $identifier
     * @return $this
     */
    public function identifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Convert the message into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $missing = new stdClass();

        $payload = [
            'campaign' => $this->campaign ?: $missing,
            'content' => $this->content,
            'ignore_user_timezone' => !$this->recipientTimezone,
            'link' => $this->url ?: $missing,
            'minimize_link' => $this->url ? $this->shortenUrl : $missing,
            'preset' => $this->preset ?: $missing,
            'send_date' => $this->when,
            'send_rate' => $this->throughput ?: $missing,
            'transactionId' => $this->identifier ?: $missing,
            'timezone' => $this->timezone ?: $missing,
        ];

        return array_filter($payload, function ($value) use ($missing) {
            return $value !== $missing;
        });
    }

    /**
     * Set the Pushwoosh preset code.
     *
     * @param string $preset
     * @return $this
     */
    public function preset($preset)
    {
        $this->preset = $preset;

        return $this;
    }

    /**
     * Throttle the message rollout.
     *
     * @param int $limit
     * @return $this
     */
    public function throttle($limit)
    {
        $this->throughput = max(100, min($limit, 1000));

        return $this;
    }

    /**
     * Set the URL the message should link to.
     *
     * @param string $url
     * @param bool $shorten
     * @return $this
     */
    public function url($url, $shorten = true)
    {
        $this->shortenUrl = $shorten;
        $this->url = $url;

        return $this;
    }

    /**
     * Respect the recipients' timezone when delivering.
     *
     * @return $this
     */
    public function useRecipientTimezone()
    {
        $this->recipientTimezone = true;

        return $this;
    }
}
