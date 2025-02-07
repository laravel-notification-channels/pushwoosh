<?php

namespace NotificationChannels\Pushwoosh\Tests\Unit;

use DateTime;
use DateTimeZone;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use NotificationChannels\Pushwoosh\PushwooshMessage;
use PHPUnit\Framework\TestCase;

class PushwooshMessageTest extends TestCase
{
    /**
     * Get a fresh notification.
     *
     * @return \Illuminate\Notifications\Notification
     */
    protected function newNotification()
    {
        return tap(new Notification, function (Notification $notification) {
            $notification->id = Str::random(24);
        });
    }

    /**
     * Test if the required attributes are present upon serialization.
     *
     * @return void
     */
    public function testRequiredAttributesArePresent()
    {
        $payload = (new PushwooshMessage)->jsonSerialize();

        $this->assertIsArray($payload);
        $this->assertArrayHasKey('content', $payload);
        $this->assertIsString($payload['content']);
        $this->assertArrayHasKey('ignore_user_timezone', $payload);
        $this->assertTrue($payload['ignore_user_timezone']);
        $this->assertArrayHasKey('send_date', $payload);
        $this->assertEquals('now', $payload['send_date']);
    }

    /**
     * Test modification of the campaign code.
     *
     * @return void
     */
    public function testCampaignModification()
    {
        $message = new PushwooshMessage();
        $this->assertArrayNotHasKey('campaign', $message->jsonSerialize());

        $message = (new PushwooshMessage)->campaign('foo');
        $this->assertArrayHasKey('campaign', $message->jsonSerialize());
        $this->assertEquals('foo', $message->jsonSerialize()['campaign']);
    }

    /**
     * Test modification of the message content.
     *
     * @return void
     * @depends testRequiredAttributesArePresent
     */
    public function testContentModification()
    {
        $message = (new PushwooshMessage)->content('foo');
        $this->assertEquals('foo', $message->jsonSerialize()['content']);

        $message = (new PushwooshMessage)->content('bar', 'baz');
        $this->assertEquals(['baz' => 'bar'], $message->jsonSerialize()['content']);
    }

    /**
     * Test modification of the delivery moment.
     *
     * @return void
     * @throws \Exception
     * @depends testRequiredAttributesArePresent
     */
    public function testDeliveryMomentModification()
    {
        $message = (new PushwooshMessage)->deliverAt('2019-02-07 19:33');
        $this->assertEquals('2019-02-07 19:33', $message->jsonSerialize()['send_date']);
        $this->assertArrayNotHasKey('timezone', $message->jsonSerialize());

        $message = (new PushwooshMessage)->deliverAt('2019-01-09 03:57', 'Europe/Amsterdam');
        $this->assertEquals('2019-01-09 03:57', $message->jsonSerialize()['send_date']);
        $this->assertArrayHasKey('timezone', $message->jsonSerialize());
        $this->assertEquals('Europe/Amsterdam', $message->jsonSerialize()['timezone']);

        $datetime = new DateTime('2019-05-23 09:49', new DateTimeZone('Australia/Brisbane'));
        $message = (new PushwooshMessage)->deliverAt($datetime);
        $this->assertEquals('2019-05-23 09:49', $message->jsonSerialize()['send_date']);
        $this->assertArrayHasKey('timezone', $message->jsonSerialize());
        $this->assertEquals('Australia/Brisbane', $message->jsonSerialize()['timezone']);

        $message = (new PushwooshMessage)->deliverAt('2019-03-19 21:50', new DateTimeZone('Atlantic/Bermuda'));
        $this->assertEquals('2019-03-19 21:50', $message->jsonSerialize()['send_date']);
        $this->assertArrayHasKey('timezone', $message->jsonSerialize());
        $this->assertEquals('Atlantic/Bermuda', $message->jsonSerialize()['timezone']);
    }

    /**
     * Test the modification of the identifier.
     *
     * @return void
     */
    public function testIdentifierModification()
    {
        $message = new PushwooshMessage();
        $this->assertArrayNotHasKey('transactionId', $message->jsonSerialize());

        $message->identifier('foo');
        $this->assertArrayHasKey('transactionId', $message->jsonSerialize());
        $this->assertEquals('foo', $message->jsonSerialize()['transactionId']);
    }

    /**
     * Test the association of message to notification.
     *
     * @return void
     * @depends testIdentifierModification
     */
    public function testNotificationAssociation()
    {
        $message = new PushwooshMessage();
        $notification = $this->newNotification();

        $message->associate($notification);
        $this->assertArrayHasKey('transactionId', $message->jsonSerialize());
        $this->assertEquals($notification->id, $message->jsonSerialize()['transactionId']);

        $message->identifier('foo')->associate($notification);
        $this->assertArrayHasKey('transactionId', $message->jsonSerialize());
        $this->assertEquals('foo', $message->jsonSerialize()['transactionId']);
    }

    /**
     * Test modification of the preset code.
     *
     * @return void
     */
    public function testPresetModification()
    {
        $message = new PushwooshMessage();
        $this->assertArrayNotHasKey('preset', $message->jsonSerialize());

        $message->preset('foo');
        $this->assertArrayHasKey('preset', $message->jsonSerialize());
        $this->assertEquals('foo', $message->jsonSerialize()['preset']);
    }

    /**
     * Test modification of the root parameters.
     *
     * @return void
     */
    public function testRootParameterModification()
    {
        $message = new PushwooshMessage();
        $params = ['foo' => 'bar'];

        $this->assertArrayNotHasKey('android_root_params', $message->jsonSerialize());
        $this->assertArrayNotHasKey('ios_root_params', $message->jsonSerialize());

        $message->with('foo', 'bar');

        $this->assertArrayHasKey('android_root_params', $message->jsonSerialize());
        $this->assertEquals($params, $message->jsonSerialize()['android_root_params']);
        $this->assertArrayHasKey('ios_root_params', $message->jsonSerialize());
        $this->assertEquals($params, $message->jsonSerialize()['ios_root_params']);
    }

    /**
     * Test modification of the root parameters (Android only).
     *
     * @return void
     */
    public function testRootParameterModificationAndroid()
    {
        $message = new PushwooshMessage();
        $params = ['foo' => 'bar'];

        $message->with('foo', 'bar', 'android');

        $this->assertArrayHasKey('android_root_params', $message->jsonSerialize());
        $this->assertArrayNotHasKey('ios_root_params', $message->jsonSerialize());
        $this->assertEquals($params, $message->jsonSerialize()['android_root_params']);
    }

    /**
     * Test modification of the root parameters (iOS only).
     *
     * @return void
     */
    public function testRootParameterModificationIos()
    {
        $message = new PushwooshMessage();
        $params = ['foo' => 'bar'];

        $message->with('foo', 'bar', 'ios');

        $this->assertArrayNotHasKey('android_root_params', $message->jsonSerialize());
        $this->assertArrayHasKey('ios_root_params', $message->jsonSerialize());
        $this->assertEquals($params, $message->jsonSerialize()['ios_root_params']);
    }

    /**
     * Test modification of rollout throughput.
     *
     * @return void
     */
    public function testThroughputModification()
    {
        $message = new PushwooshMessage();
        $this->assertArrayNotHasKey('send_rate', $message->jsonSerialize());

        $message->throttle(10);
        $this->assertArrayHasKey('send_rate', $message->jsonSerialize());
        $this->assertEquals(100, $message->jsonSerialize()['send_rate']);

        $message->throttle(100);
        $this->assertArrayHasKey('send_rate', $message->jsonSerialize());
        $this->assertEquals(100, $message->jsonSerialize()['send_rate']);

        $message->throttle(1000);
        $this->assertArrayHasKey('send_rate', $message->jsonSerialize());
        $this->assertEquals(1000, $message->jsonSerialize()['send_rate']);

        $message->throttle(10000);
        $this->assertArrayHasKey('send_rate', $message->jsonSerialize());
        $this->assertEquals(1000, $message->jsonSerialize()['send_rate']);
    }

    /**
     * Test modification of the URL.
     *
     * @return void
     */
    public function testUrlModification()
    {
        $message = new PushwooshMessage();
        $this->assertArrayNotHasKey('link', $message->jsonSerialize());
        $this->assertArrayNotHasKey('minimize_link', $message->jsonSerialize());

        $message->url('https://google.com');
        $this->assertArrayHasKey('link', $message->jsonSerialize());
        $this->assertArrayHasKey('minimize_link', $message->jsonSerialize());
        $this->assertEquals('https://google.com', $message->jsonSerialize()['link']);
        $this->assertTrue($message->jsonSerialize()['minimize_link']);

        $message->url('https://google.com', false);
        $this->assertArrayHasKey('link', $message->jsonSerialize());
        $this->assertArrayHasKey('minimize_link', $message->jsonSerialize());
        $this->assertEquals('https://google.com', $message->jsonSerialize()['link']);
        $this->assertFalse($message->jsonSerialize()['minimize_link']);
    }

    /**
     * Test modification of the timezone strategy.
     *
     * @return void
     * @depends testRequiredAttributesArePresent
     */
    public function testTimezoneStrategyModification()
    {
        $message = new PushwooshMessage();
        $this->assertTrue($message->jsonSerialize()['ignore_user_timezone']);

        $message = (new PushwooshMessage)->useRecipientTimezone();
        $this->assertFalse($message->jsonSerialize()['ignore_user_timezone']);
    }

    /**
     * Test modification of the silent parameters .
     *
     * @return void
     */
    public function testSilentModification()
    {
        $message = new PushwooshMessage();
        $message->silent(true);
        $this->assertArrayHasKey('ios_silent', $message->jsonSerialize());
        $this->assertEquals(1, $message->jsonSerialize()['ios_silent']);
        $this->assertArrayHasKey('android_silent', $message->jsonSerialize());
        $this->assertEquals(1, $message->jsonSerialize()['android_silent']);
    }

    /**
     * Test modification of the silent parameters .
     *
     * @return void
     */
    public function testSilentModificationNull()
    {
        $message = new PushwooshMessage();
        $message->silent();
        $this->assertArrayNotHasKey('ios_silent', $message->jsonSerialize());
        $this->assertArrayNotHasKey('android_silent', $message->jsonSerialize());
    }
}
