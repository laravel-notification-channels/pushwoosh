<?php

namespace NotificationChannels\Pushwoosh\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Event;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use NotificationChannels\Pushwoosh\Events\UnknownDevices;
use NotificationChannels\Pushwoosh\Exceptions\PushwooshException;
use NotificationChannels\Pushwoosh\Pushwoosh;
use NotificationChannels\Pushwoosh\PushwooshPendingMessage;
use NotificationChannels\Pushwoosh\Tests\TestCase;

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
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $this->app->instance(Client::class, $this->client = Mockery::mock(Client::class));
        $this->pushwoosh = $this->app->make(Pushwoosh::class);
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

        $this->pushwoosh->createMessage(
            new PushwooshPendingMessage($this->pushwoosh)
        );

        Event::assertDispatched(UnknownDevices::class, function (UnknownDevices $event) {
            return $event->message === 'AF0B-EEFE4D5E-E445B2E9'
                && $event->devices === ['foo', 'bar'];
        });
    }
}
