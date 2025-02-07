<?php

namespace NotificationChannels\Pushwoosh;

use DateTimeInterface;
use DateTimeZone;
use Illuminate\Notifications\Notification;
use InvalidArgumentException;
use JsonSerializable;

class PushwooshMessage implements JsonSerializable
{
    protected $androidRootParameters;
    protected $campaign;
    protected $content;
    protected $data;
    protected $identifier;
    protected $iosRootParameters;
    protected $preset;
    protected $recipientTimezone;
    protected $shortenUrl;
    protected $timezone;
    protected $title;
    protected $throughput;
    protected $url;
    protected $when;
    protected $androidSilent = null;
    protected $iosSilent = null;

    /**
     * Create a new push message.
     *
     * @param string $content
     * @return void
     */
    public function __construct(string $content = '')
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
    public function campaign(string $campaign)
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
    public function content(string $content, string $language = null)
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
    public function identifier(string $identifier)
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
        $payload = [
            'android_root_params' => $this->androidRootParameters,
            'campaign' => $this->campaign,
            'chrome_title' => $this->title,
            'content' => $this->content,
            'data' => $this->data,
            'firefox_title' => $this->title,
            'ignore_user_timezone' => !$this->recipientTimezone,
            'ios_root_params' => $this->iosRootParameters,
            'ios_title' => $this->title,
            'link' => $this->url,
            'minimize_link' => $this->url ? $this->shortenUrl : null,
            'preset' => $this->preset,
            'safari_title' => $this->title,
            'send_date' => $this->when,
            'send_rate' => $this->throughput,
            'transactionId' => $this->identifier,
            'timezone' => $this->timezone,
            'ios_silent' => $this->iosSilent,
            'android_silent' => $this->androidSilent,
        ];

        return array_filter($payload, function ($value) {
            return $value !== null;
        });
    }

    /**
     * Set the Pushwoosh preset code.
     *
     * @param string $preset
     * @return $this
     */
    public function preset(string $preset)
    {
        $this->preset = $preset;

        return $this;
    }

    /**
     * Set the message title (only supported on Chrome, Firefox, iOS and Safari).
     *
     * @param string|null $title
     * @return $this
     */
    public function title(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Throttle the message rollout.
     *
     * @param int $limit
     * @return $this
     */
    public function throttle(int $limit)
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
    public function url(string $url, bool $shorten = true)
    {
        $this->shortenUrl = $shorten;
        $this->url = $url;

        return $this;
    }

    /**
     * Add a root level parameter.
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $platform
     * @return $this
     */
    public function with(string $key, $value, string $platform = null)
    {
        if (!in_array($platform, [null, 'ios', 'android'])) {
            throw new InvalidArgumentException("Invalid platform {$platform}");
        }

        if (($platform ?: 'android') === 'android') {
            $this->androidRootParameters[$key] = $value;
            $this->data[$key] = $value; # android_root_params seems to (not always) work
        }

        if (($platform ?: 'ios') === 'ios') {
            $this->iosRootParameters[$key] = $value;
        }

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

    /**
     * Enable or disable silent notifications for both Android and iOS platforms.
     *
     * @param bool $silent
     * @return $this
     */
    public function silent(bool $silent = true)
    {
        if ($silent) {
            $this->androidSilent = 1;
            $this->iosSilent = 1;
        }

        return $this;
    }
}
