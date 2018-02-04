<?php

namespace Partnermarketing\Queue\Test\Listener;

use Partnermarketing\Queue\Entity\Queue;
use Partnermarketing\Queue\Entity\Stream;
use Partnermarketing\Queue\Listener\CallbackQueueListener;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

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

        return 'RETURN_VALUE';
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

        $this->assertEquals(
            'RETURN_VALUE',
            $this->listener->execute($array)
        );

        $this->assertEquals($array, $this->event);
    }

    /**
     * Tests that isComplete() initially reports as false
     */
    public function testIsCompleteDefaultsToFalse()
    {
        $this->assertFalse($this->listener->isComplete());
    }

    /**
     * Tests that when complete is set, isComplete() reports as true
     */
    public function testIsCompleteTrue()
    {
        $reflect = new ReflectionClass(CallbackQueueListener::class);
        $complete = $reflect->getProperty('complete');
        $complete->setAccessible(true);
        $complete->setValue($this->listener, true);

        $this->assertTrue($this->listener->isComplete());
    }
}
