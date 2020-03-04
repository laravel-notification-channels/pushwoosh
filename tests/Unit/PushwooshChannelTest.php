<?php

namespace NotificationChannels\Pushwoosh\Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use NotificationChannels\Pushwoosh\Pushwoosh;
use NotificationChannels\Pushwoosh\PushwooshChannel;
use NotificationChannels\Pushwoosh\PushwooshPendingMessage;
use PHPUnit\Framework\TestCase;

class PushwooshChannelTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var \Illuminate\Notifications\RoutesNotifications|\Mockery\MockInterface
     */
    protected $notifiable;

    /**
     * @var \Illuminate\Notifications\Notification|\Mockery\MockInterface
     */
    protected $notification;

    /**
     * @var \NotificationChannels\Pushwoosh\PushwooshChannel
     */
    protected $channel;

    /**
     * @var \NotificationChannels\Pushwoosh\PushwooshPendingMessage|\Mockery\MockInterface
     */
    protected $queue;

    /**
     * @var \NotificationChannels\Pushwoosh\Pushwoosh|\Mockery\MockInterface
     */
    protected $pushwoosh;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->channel = new PushwooshChannel($this->pushwoosh = Mockery::mock(Pushwoosh::class));
        $this->notifiable = Mockery::mock(Model::class);
        $this->notification = Mockery::mock(Notification::class);
        $this->queue = Mockery::mock(PushwooshPendingMessage::class);
    }

    /**
     * Test if the channel message is dispatched properly.
     *
     * @return void
     */
    public function testSend()
    {
        $this->notifiable->shouldReceive('routeNotificationFor')
            ->once()
            ->with('pushwoosh', $this->notification)
            ->andReturn(Str::random(48));

        $this->notification->shouldReceive('toPushwoosh')
            ->once()
            ->with($this->notifiable)
            ->andReturn(Str::random(100));

        $this->pushwoosh
            ->shouldReceive('send')
            ->once()
            ->andReturn($this->queue);

        $this->queue
            ->shouldReceive('to')
            ->once()
            ->andReturn($this->queue);

        $this->channel->send($this->notifiable, $this->notification);
    }

    /**
     * Test if the channel stops when the notification returns an empty message.
     *
     * @return void
     */
    public function testEmptyMessage()
    {
        $this->notifiable->shouldReceive('routeNotificationFor')
            ->once()
            ->with('pushwoosh', $this->notification)
            ->andReturn(Str::random(48));

        $this->notification->shouldReceive('toPushwoosh')
            ->once()
            ->with($this->notifiable)
            ->andReturn(null);

        $this->pushwoosh
            ->shouldNotReceive('send');

        $this->channel->send($this->notifiable, $this->notification);
    }

    /**
     * Test if the channel stops when the notifiable returns no route.
     *
     * @return void
     */
    public function testEmptyRecipient()
    {
        $this->notifiable->shouldReceive('routeNotificationFor')
            ->once()
            ->with('pushwoosh', $this->notification)
            ->andReturn(null);

        $this->notification
            ->shouldNotReceive('toPushwoosh');

        $this->pushwoosh
            ->shouldNotReceive('send');

        $this->channel->send($this->notifiable, $this->notification);
    }
}
