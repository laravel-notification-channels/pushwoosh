<?php

namespace NotificationChannels\Pushwoosh\Tests\Unit;

use InvalidArgumentException;
use NotificationChannels\Pushwoosh\PushwooshRecipient;
use PHPUnit\Framework\TestCase;

class PushwooshRecipientTest extends TestCase
{
    /**
     * Test the modification of the device list.
     *
     * @return void
     */
    public function testDeviceModification()
    {
        $recipient = (new PushwooshRecipient)->device('foo');

        $this->assertArrayHasKey('devices', $recipient->jsonSerialize());
        $this->assertEquals(['foo'], $recipient->jsonSerialize()['devices']);
    }

    /**
     * Test the modification of the platform list.
     *
     * @return void
     */
    public function testPlatformModification()
    {
        $recipient = (new PushwooshRecipient)->platform('ios');

        $this->assertArrayHasKey('platforms', $recipient->jsonSerialize());
        $this->assertEquals([1], $recipient->jsonSerialize()['platforms']);

        $this->expectException(InvalidArgumentException::class);
        (new PushwooshRecipient)->platform('Windows 98');
    }

    /**
     * Test the modification of the user list.
     *
     * @return void
     */
    public function testUserModification()
    {
        $recipient = (new PushwooshRecipient)->user('foo');

        $this->assertArrayHasKey('users', $recipient->jsonSerialize());
        $this->assertEquals(['foo'], $recipient->jsonSerialize()['users']);
    }
}
