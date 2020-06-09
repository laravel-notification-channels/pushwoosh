# Pushwoosh notification channel for Laravel

<p align="center">
    <a href="https://travis-ci.org/laravel-notification-channels/pushwoosh">
        <img src="https://travis-ci.org/laravel-notification-channels/pushwoosh.svg?branch=master" alt="Build status">
    </a>
    <a href="https://packagist.org/packages/laravel-notification-channels/pushwoosh">
        <img src="https://poser.pugx.org/laravel-notification-channels/pushwoosh/downloads" alt="Downloads">
    </a>
    <a href="https://packagist.org/packages/laravel-notification-channels/pushwoosh">
        <img src="https://poser.pugx.org/laravel-notification-channels/pushwoosh/v/stable" alt="Latest release">
    </a>
    <a href="https://scrutinizer-ci.com/g/laravel-notification-channels/pushwoosh/">
        <img src="https://scrutinizer-ci.com/g/laravel-notification-channels/pushwoosh/badges/coverage.png?b=master" alt="Code coverage">
    </a>
    <a href="LICENSE.md">
        <img src="https://poser.pugx.org/laravel-notification-channels/pushwoosh/license" alt="License">
    </a>
</p>

This package makes sending notifications using [Pushwoosh](https://www.pushwoosh.com/) a breeze.

## Contents
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
    - [Routing notifications](#routing-notifications)
    - [Sending notifications](#sending-notifications)
    - [Available methods](#available-methods)
- [Changelog](#changelog)
- [Testing](#testing)
- [Security](#security)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)

## Requirements
This make use of this package you need:
- Laravel 5.5 or higher
- PHP 7.1 or higher
- An active Pushwoosh subscription (and use at least one Pushwoosh SDK)

## Installation
To install this package run the following command:

```bash
composer require laravel-notification-channels/pushwoosh
```

Next, add the following lines to your `config/services.php`:

```php
'pushwoosh' => [
    'application' => env('PUSHWOOSH_APP_CODE'),
    'token' => env('PUSHWOOSH_TOKEN'),
],
```

You can now add the `PUSHWOOSH_APP_CODE` (found [here](https://go.pushwoosh.com/v2/applications)) and the
`PUSHWOOSH_TOKEN` (found [here](https://go.pushwoosh.com/v2/api_access)) to your environment file.

## Usage
Using this package, you can use Pushwoosh just like any other notification channel within Laravel. For more information
about Laravel's notification system, see the [official documentation](https://laravel.com/docs/master/notifications).

Note that before you can start sending pushes you must first register users to your application using one of
[Pushwoosh's SDKs](https://docs.pushwoosh.com/platform-docs/getting-started/untitled-2).

### Routing notifications
In order for Pushwoosh to know to what devices it needs to send to, you will need to add the
`routeNotificationForPushwoosh` to your notifiable model(s), for example:

```php
class Customer extends Model
{
    use Notifiable;
    
    public function routeNotificationForPushwoosh()
    {
        // In this example 'device_id' is a token previously
        // retrieved from Pushwoosh using one of their SDKs
        return (new PushwooshRecipient)->device($this->device_id);
    }
}
```

The `routeNotificationForPushwoosh` method may return a string, an array of strings or a `PushwooshRecipient` instance.
For more information about the `PushwooshRecipient` class refer to the [available methods](#pushwooshrecipient) section.

### Sending notifications
Sending a pushwoosh message is easy, add `pushwoosh` to your notification's via method and implement the `toPushwoosh`
method, for example:

```php
class WishlistItemOnSale extends Notification
{
    public function via($notifiable)
    {
        return ['pushwoosh'];
    }
    
    public function toPushwoosh($notifiable)
    {
        return (new PushwooshMessage)
            ->content('Your wishlist item ' . $this->product->name . ' is on sale, get it now!')
            ->url(route('products.show', $this->product))
            ->deliverAt(Carbon::now()->addMinutes(10));
    }
}
```

> The `toPushwoosh` method may return a string or an instance of the `PushwooshMessage` class, for more information on
the `PushwooshMessage` class refer to the [available methods](#pushwooshmessage) section.

You can then send a push to one user:
```php
$customer->notify(new WishlistItemOnSale($product));
```

Or to multiple users:
```php
Notification::send($customers, new WishlistItemOnSale($product));
```

### Available methods
This section details the public API of this package.

#### PushwooshMessage
Below is a list of available methods on the `PushwooshMessage` class.

Method                           | Description
---------------------------------|---
`campaign($campaign)`            | Set the Pushwoosh campaign code
`content($content[, $language])` | Set the message content (optionally for a specific language)
`deliverAt($when[, $timezone])`  | Set the delivery moment
`identifier($identifier)`        | Set the Pushwoosh unique identifier (defaults to the notification ID)
`preset($preset)`                | Set the Pushwoosh preset code
`throttle($limit)`               | Throttle the rollout (100-1000 pushes per second)
`title($title)`                  | Set the message title (only on Chrome, Firefox, iOS and Safari)
`url($url[, $shorten])`          | Set the URL the message should link to
`useRecipientTimezone()`         | Respect the recipients' timezone when delivering the message
`with($key, $value[, $platform])`| Add a root level parameter.

#### PushwooshRecipient
Below is a list of available methods on the `PushwooshRecipient` class.

Method                       | Description
-----------------------------|---
`device($device[, ...])`     | Limit the delivery to the given device(s)
`platform($platform[, ...])` | Limit the delivery to the given [platform(s)](#platforms)
`user($user[, ...])`         | Limit the delivery to the given  [user(s)](https://www.pushwoosh.com/platform-docs/api-reference/user-centric-api)
`within($lat, $lng, $range)` | Limit the delivery to the given geo zone

##### Platforms
Below is a list of supported platforms, for the `PushwooshRecipient::platform` method.

- Amazon
- Android
- Blackberry
- Chrome
- Firefox
- iOS
- Mac
- Safari
- Windows
- Windows Phone

## Changelog
Please see the [changelog](CHANGELOG.md) for more information on what has changed recently.

## Testing
``` bash
composer test
```

## Contributing
If you want to contribute to this package, take a look at the [contribution guide](CONTRIBUTING.md).

## Credits
- [Choraimy Kroonstuiver](https://github.com/axlon)
- [All Contributors](../../contributors)

## License
This product is licensed under the MIT License (MIT). Please see the [License File](LICENSE.md) for more information.
