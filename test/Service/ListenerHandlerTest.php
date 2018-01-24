<?php

namespace Partnermarketing\Queue\Test\Service;

use BadMethodCallException;
use Partnermarketing\Queue\Service\ListenerHandler;
use Partnermarketing\Queue\Entity\Queue;
use Partnermarketing\Queue\Entity\Stream;
use Partnermarketing\Queue\Exception\TimeoutException;
use Partnermarketing\Queue\Listener\CallbackQueueListener;
use PHPUnit\Framework\TestCase;
use Redis;
use ReflectionClass;
use ReflectionProperty;

/**
 * Tests that the ListenerHandler correctly registers listeners and
 * handles events coming in off a queue
 */
class ListenerHandlerTest extends TestCase
{
    /**
     * The mock Redis connection that is injected into the handler
     *
     * @var Redis
     */
    private $conn;

    /**
     * The ListenerHandler we are testing
     *
     * @var ListnerHandler
     */
    private $object;

    /**
     * A reflector for the ListenerHandler so we can interact with its
     * private properties
     *
     * @var ReflectionClass
     */
    private $reflect;

    /**
     * The event callback given back when the callback is used
     *
     * @var mixed
     */
    private $event;

    /**
     * The queue being tested
     *
     * @var Queue
     */
    private $queue;

    /**
     * Sets up the commonly used items for each test
     */
    public function setUp()
    {
        $this->event = null;
        $this->reflect = new ReflectionClass(ListenerHandler::class);
        $this->object = $this->reflect->newInstanceWithoutConstructor();

        $this->conn = $this->getMockBuilder(Redis::class)
            ->disableOriginalConstructor()
            ->setMethods(['sAdd', 'brPop', 'sRem'])
            ->getMock();

        $conn = $this->reflect->getProperty('conn');
        $conn->setAccessible(true);
        $conn->setValue($this->object, $this->conn);

        $this->queue = new Queue('test', new Stream('test_stream'));
    }

    /**
     * Creates a mock QueueListener with executeCallback() used as the
     * execute callback
     */
    private function getMockQueueListener()
    {
        return new CallbackQueueListener(
            $this->queue,
            [$this, 'executeCallback']
        );
    }

    /**
     * The execute callback used by the listener, just saves the value
     * into $this->event.
     */
    public function executeCallback($event)
    {
        $this->event = $event;
    }

    /**
     * Gets the ReflectionProperty for the listeners property, setting
     * it as accessible before returning
     *
     * @return ReflectionProperty
     */
    private function getListeners()
    {
        $listenersProp = $this->reflect->getProperty('listeners');
        $listenersProp->setAccessible(true);

        return $listenersProp;
    }

    /**
     * Expects that the stream queue set will be accessed with the test
     * queue. Either with a sSet or sRem command
     *
     * @var string $command
     */
    private function expectSetCall($command)
    {
        $this->conn->expects($this->once())
            ->method($command)
            ->with('test_stream:queues', 'test');
    }

    /**
     * Tests that registerListener() method adds the given listener to
     * its internal list and that it registers it in the queues list on
     * redis
     */
    public function testRegisterListener()
    {
        $this->expectSetCall('sAdd');

        $listener = $this->getMockQueueListener();

        $this->object->registerListener($listener);

        $this->assertEquals(
            ['test_stream:queues:test' => $listener],
            $this->getListeners()->getValue($this->object)
        );
    }

    /**
     * Sets up the environment and expectations for a normal run of a
     * deregister opperation (deregistering either by queue / listener
     * when it does exist)
     */
    private function setUpSuccessfulDeregisterTest()
    {
        $this->expectSetCall('sRem');
        $this->getListeners()->setValue(
            $this->object,
            ['test_stream:queues:test' => '123']
        );
    }

    /**
     * Asserts that there are no listeners on the handler
     */
    private function assertNoListeners()
    {
        $this->assertEmpty(
            $this->getListeners()->getValue($this->object)
        );
    }

    /**
     * Tests that a listener can be deregistered by queue name
     */
    public function testDeregisterQueue()
    {
        $this->setUpSuccessfulDeregisterTest();
        $this->object->deregisterQueue($this->queue);
        $this->assertNoListeners();
    }

    /**
     * Tests that if you try to deregister a queue that doesn't exist
     * it throws an exception
     */
    public function testDeregisterQueueException()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Queue test is not registered');

        $this->object->deregisterQueue($this->queue);
    }

    /**
     * Tests that an exception is not thrown when force is specified
     */
    public function testDeregisterQueueForce()
    {
        $this->expectSetCall('sRem');

        $this->object->deregisterQueue($this->queue, true);
    }

    /**
     * Tests that a listener can be deregistered by listener
     */
    public function testDeregisterListener()
    {
        $this->setUpSuccessfulDeregisterTest();
        $this->object->deregisterListener(
            $this->getMockQueueListener()
        );
        $this->assertNoListeners();
    }

    /**
     * Tests that the listenOnce() method uses a BRPOP command on redis
     * to wait for events on the queues it is listening to, and that
     * these are handled correctly
     */
    public function testListenOnce()
    {
        $self = $this;
        $this->conn->expects($this->once())
            ->method('brPop')
            ->will($this->returnCallback(
                function($lists, $timeout) use ($self) {
                    $this->assertSame(
                        ['test_stream:queues:test'],
                        $lists
                    );
                    $this->assertSame(5, $timeout);

                    return [
                        'test_stream:queues:test',
                        '{"event":"something"}'
                    ];
                }
            ));

        $this->getListeners()->setValue(
            $this->object,
            ['test_stream:queues:test' => $this->getMockQueueListener()]
        );

        $this->object->listenOnce(5);

        $this->assertEquals(['event' => 'something'], $this->event);
    }

    /**
     * Tests that when the connection returns nothing, it correctly
     * identifies that a timeout has occured and throws an exception
     */
    public function testListenOnceTimeout()
    {
        $this->getListeners()->setValue(
            $this->object,
            ['test_stream:queues:test' => null]
        );
        $this->conn->expects($this->once())
            ->method('brPop')
            ->with(['test_stream:queues:test'], 25)
            ->willReturn(null);
        $this->expectException(TimeoutException::class);
        $this->expectExceptionMessage('Timed out waiting for events');

        $this->object->listenOnce(25);
    }
}

