<?php

namespace Partnermarketing\Queue\Test\Service;

use Partnermarketing\Queue\Entity\Queue;
use Partnermarketing\Queue\Entity\Stream;
use Partnermarketing\Queue\Service\CallbackQueueListener;
use PHPUnit\Framework\TestCase;

/**
 * Tests that the CallbackQueueListener (and by extension the
 * AbstractQueueListener) works as expected
 */
class CallbackQueueListenerTest extends TestCase
{
    /**
     * The queue the listener is for
     */
    private $queue;

    /**
     * The listener we are testing
     */
    private $listener;

    /**
     * If an event is successfully called back, it should be saved here
     */
    private $event;

    /**
     * Sets up the test
     */
    public function setUp()
    {
        $this->queue = new Queue('test', new Stream('test_stream'));
        $this->listener = new CallbackQueueListener(
            $this->queue,
            [$this, 'executeCallback']
        );
        $this->event = null;
    }

    /**
     * This is the callback used for the test listener
     *
     * @param array $event
     */
    public function executeCallback($event)
    {
        $this->event = $event;
    }

    /**
     * Tests that getQueue() returns the queue that was given in the
     * constructor
     */
    public function testGetQueue()
    {
        $this->assertSame($this->queue, $this->listener->getQueue());
    }

    /**
     * Tests that execute() calls the callback we set in the constructor
     */
    public function testExecute()
    {
        $array = ['1', '2', '3'];

        $this->listener->execute($array);

        $this->assertEquals($array, $this->event);
    }
}
