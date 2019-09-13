<?php

namespace NotificationChannels\Pushwoosh\Tests\Unit;

use Mockery;
use NotificationChannels\Pushwoosh\Pushwoosh;
use NotificationChannels\Pushwoosh\PushwooshMessage;
use NotificationChannels\Pushwoosh\PushwooshPendingMessage;
use PHPUnit\Framework\TestCase;

class PushwooshPendingMessageTest extends TestCase
{
    /**
     * @var \Mockery\MockInterface|\NotificationChannels\Pushwoosh\Pushwoosh
     */
    protected $pushwoosh;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->pushwoosh = Mockery::mock(Pushwoosh::class);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        Mockery::close();
    }

    /**
     * Test if the createMessage API call is made when a pending message is destroyed.
     *
     * @return void
     * @doesNotPerformAssertions
     */
    public function testDispatchUponDestruction()
    {
        $this->pushwoosh->shouldReceive('createMessage')
            ->once();

        (new PushwooshPendingMessage($this->pushwoosh))->queue(
            new PushwooshMessage('foo')
        );
    }

    /**
     * Test if no createMessage API call is made when an empty pending message is destroyed.
     *
     * @return void
     * @doesNotPerformAssertions
     */
    public function testNoDispatchIfQueueIsEmpty()
    {
        $this->pushwoosh->shouldNotReceive('createMessage');

        new PushwooshPendingMessage($this->pushwoosh);
    }
}
