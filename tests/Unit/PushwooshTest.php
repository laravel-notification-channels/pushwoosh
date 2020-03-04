<?php

namespace NotificationChannels\Pushwoosh\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Str;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use NotificationChannels\Pushwoosh\Exceptions\PushwooshException;
use NotificationChannels\Pushwoosh\Exceptions\UnknownDeviceException;
use NotificationChannels\Pushwoosh\Pushwoosh;
use NotificationChannels\Pushwoosh\PushwooshPendingMessage;
use PHPUnit\Framework\TestCase;

class PushwooshTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var \GuzzleHttp\ClientInterface|\Mockery\MockInterface
     */
    protected $client;

    /**
     * @var \NotificationChannels\Pushwoosh\Pushwoosh
     */
    protected $pushwoosh;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->client = Mockery::mock(Client::class);
        $this->pushwoosh = new Pushwoosh($this->client, Str::random(8), Str::random(24));
    }

    /**
     * Test if an API error is handled properly.
     *
     * @return void
     */
    public function testApiError()
    {
        $this->client->shouldReceive('send')->once()->andReturn(
            new Response(200, [], file_get_contents(__DIR__ . '/../Fixtures/access-denied.json'))
        );

        $this->expectException(PushwooshException::class);
        $this->expectExceptionMessage('Access denied or application not found');

        $this->pushwoosh->createMessage(
            new PushwooshPendingMessage($this->pushwoosh)
        );
    }

    /**
     * Test if message created response is handled properly.
     *
     * @return void
     */
    public function testSuccessResponse()
    {
        $this->client->shouldReceive('send')->once()->andReturn(
            new Response(200, [], file_get_contents(__DIR__ . '/../Fixtures/message-created.json'))
        );

        $response = $this->pushwoosh->createMessage(
            new PushwooshPendingMessage($this->pushwoosh)
        );

        $this->assertEquals(['43BC-9C338F44-AAFC439A'], $response);
    }

    /**
     * Test if an unknown devices response is handled properly.
     *
     * @return void
     */
    public function testUnknownDevices()
    {
        $this->client->shouldReceive('send')->once()->andReturn(
            new Response(200, [], file_get_contents(__DIR__ . '/../Fixtures/unknown-devices.json'))
        );

        $this->expectException(UnknownDeviceException::class);
        $this->expectExceptionMessage('Unknown device(s) referenced: foo, bar');

        $this->pushwoosh->createMessage(
            new PushwooshPendingMessage($this->pushwoosh)
        );
    }
}
