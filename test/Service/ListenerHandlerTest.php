<?php

namespace Partnermarketing\Queue\Test\Service;

use Partnermarketing\Queue\Service\ListenerHandler;
use Partnermarketing\Queue\Entity\Queue;
use Partnermarketing\Queue\Entity\Stream;
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
     * Sets up the commonly used items for each test
     */
    public function setUp()
    {
        $this->event = null;
        $this->reflect = new ReflectionClass(ListenerHandler::class);
        $this->object = $this->reflect->newInstanceWithoutConstructor();

        $this->conn = $this->getMockBuilder(Redis::class)
            ->disableOriginalConstructor()
            ->setMethods(['sAdd', 'brPop'])
            ->getMock();

        $conn = $this->reflect->getProperty('conn');
        $conn->setAccessible(true);
        $conn->setValue($this->object, $this->conn);
    }

    /**
     * Creates a mock QueueListener with executeCallback() used as the
     * execute callback
     */
    private function getMockQueueListener()
    {
        return new CallbackQueueListener(
            new Queue('test', new Stream('test_stream')),
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
     * Tests that registerListener() method adds the given listener to
     * its internal list and that it registers it in the queues list on
     * redis
     */
    public function testRegisterListener()
    {
        $self = $this;
        $this->conn->expects($this->once())
            ->method('sAdd')
            ->will($this->returnCallback(
                function($set, $value) use ($self) {
                    $self->assertSame('test_stream:queues', $set);
                    $self->assertSame('test', $value);
                }
            ));

        $listener = $this->getMockQueueListener();

        $this->object->registerListener($listener);

        $this->assertEquals(
            ['test_stream:queues:test' => $listener],
            $this->getListeners()->getValue($this->object)
        );
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
}

