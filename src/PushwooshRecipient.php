<?php

namespace NotificationChannels\Pushwoosh;

use Illuminate\Support\Str;
use InvalidArgumentException;
use JsonSerializable;

class PushwooshRecipient implements JsonSerializable
{
    protected $devices;
    protected $geoZone;
    protected $platforms;
    protected $users;

    /**
     * Create a new recipient.
     *
     * @return void
     */
    public function __construct()
    {
        $this->devices = [];
        $this->platforms = [];
        $this->users = [];
    }

    /**
     * Set the device(s).
     *
     * @param string ...$devices
     * @return $this
     */
    public function device(string ...$devices)
    {
        $this->devices = array_merge($this->devices, $devices);
        $this->users = [];

        return $this;
    }

    protected static function getSupportedPlatforms()
    {
        return [
            'amazon' => 9,
            'android' => 3,
            'blackberry' => 2,
            'chrome' => 11,
            'firefox' => 12,
            'ios' => 1,
            'mac' => 7,
            'safari' => 10,
            'windows' => 8,
            'windows_phone' => 5,
        ];
    }

    /**
     * Convert the recipient to something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_filter([
            'devices' => $this->devices,
            'geozone' => $this->geoZone,
            'platforms' => $this->platforms,
            'users' => $this->users,
        ]);
    }

    /**
     * Set the platform(s).
     *
     * @param string ...$platforms
     * @return $this
     */
    public function platform(string ...$platforms)
    {
        $supported = static::getSupportedPlatforms();

        foreach ($platforms as $platform) {
            $name = Str::slug(strtolower($platform));

            if (!array_key_exists($name, $supported)) {
                throw new InvalidArgumentException("Unsupported platform $platform");
            }

            $this->platforms[] = $supported[$name];
        }

        return $this;
    }

    /**
     * Set the user(s).
     *
     * @param string ...$users
     * @return $this
     */
    public function user(string ...$users)
    {
        $this->devices = [];
        $this->users = array_merge($this->users, $users);

        return $this;
    }

    /**
     * Set the geo zone.
     *
     * @param float $latitude
     * @param float $longitude
     * @param int $range
     * @return $this
     */
    public function within(float $latitude, float $longitude, int $range)
    {
        $this->geoZone = [
            'lat' => $latitude,
            'lng' => $longitude,
            'range' => $range,
        ];

        return $this;
    }
}
