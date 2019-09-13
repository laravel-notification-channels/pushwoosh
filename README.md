# Pushwoosh notification channel for Laravel
**TODO** - Add integration badges here once package is moved

This package makes sending notifications using [Pushwoosh](https://www.pushwoosh.com/) a breeze.

## Contents
- [Requirements](#requirements)
- [Installation](#installation)
    - [Laravel 5.1 and 5.2](#laravel-51-and-52)
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
- Laravel 5.1 or higher
- PHP 5.6 or higher
- An active Pushwoosh subscription

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

If you are using Laravel 5.5 or higher you are now done, continue to the [usage](#usage) section. If not, add the
following line to your `config/app.php`:

```php
'providers' => [
    ...
    NotificationChannels\Pushwoosh\PushwooshServiceProvider::class,
    ...
],
```

### Laravel 5.1 and 5.2
Laravel 5.1 and 5.2 did not include a notification system so you will have to install the
[backport](https://github.com/laravel-notification-channels/backport) to make this package work on those versions.

## Usage
Using this package, you can use Pushwoosh just like any other notification channel within Laravel. For more information
about Laravel's notification system, see the [official documentation](https://laravel.com/docs/master/notifications).

### Routing notifications
In order for Pushwoosh to know to what devices it needs to send to, you will need to add the
`routeNotificationForPushwoosh` to your notifiable model(s), for example:

```php
class Customer extends Model
{
    use Notifiable;
    
    public function routeNotificationForPushwoosh()
    {
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

The `toPushwoosh` method may return a string or an instance of the `PushwooshMessage` class, for more information on the
`PushwooshMessage` class refer to the [available methods](#pushwooshmessage) section.

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
`url($url[, $shorten])`          | Set the URL the message should link to
`useRecipientTimezone()`         | Respect the recipients' timezone when delivering the message

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

## Security
If you discover any security related issues, please email choraimy@live.nl instead of using the issue tracker.

## Contributing
If you want to contribute to this package, take a look at the [contribution guide](CONTRIBUTING.md).

## Credits
- [Choraimy Kroonstuiver](https://github.com/axlon)
- [All Contributors](../../contributors)

## License
This product is licensed under the MIT License (MIT). Please see the [License File](LICENSE.md) for more information.
