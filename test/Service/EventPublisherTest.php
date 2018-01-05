<?php

namespace Partnermarketing\Queue\Test\Service;

use Partnermarketing\Queue\Service\EventPublisher;
use Partnermarketing\Queue\Entity\Queue;
use Partnermarketing\Queue\Entity\Stream;
use PHPUnit\Framework\TestCase;
use Redis;
use ReflectionClass;
use ReflectionProperty;

/**
 */
class EventPublisherTest extends TestCase
{
    /**
     * The mock Redis connection that is injected into the handler
     *
     * @var Redis
     */
    private $conn;

    /**
     * The EventPublisher we are testing
     *
     * @var EventPublisher
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
     * Sets up the commonly used items for each test
     */
    public function setUp()
    {
        $this->event = null;
        $this->reflect = new ReflectionClass(EventPublisher::class);
        $this->object = $this->reflect->newInstanceWithoutConstructor();

        $this->conn = $this->getMockBuilder(Redis::class)
            ->disableOriginalConstructor()
            ->setMethods(['sMembers', 'lPush'])
            ->getMock();

        $self = $this;
        $this->conn->expects($this->once())
            ->method('sMembers')
            ->will($this->returnCallback(function($set) use ($self) {
                $this->assertSame('test_stream:queues', $set);
                return ['test'];
            }));

        $conn = $this->reflect->getProperty('conn');
        $conn->setAccessible(true);
        $conn->setValue($this->object, $this->conn);
    }

    /**
     * Tests that getStreamQueues() queues the redis SET for the
     * stream and returns wrapped Queue objects
     */
    public function testGetStreamQueues()
    {
        $return = iterator_to_array($this->object->getStreamQueues(
            new Stream('test_stream')
        ));

        $this->assertCount(1, $return);
        $this->assertInstanceOf(Queue::class, $return[0]);
        $this->assertSame('test', $return[0]->getName());
    }

    /**
     * Tests that addEvent() correctly encodes the event and adds it to
     * all of the correct queues
     */
    public function testAddEvent()
    {
        $self = $this;
        $this->conn->expects($this->once())
            ->method('lPush')
            ->will($this->returnCallback(
                function($list, $data) use ($self) {
                    $this->assertSame('test_stream:queues:test', $list);
                    $this->assertSame('{"event":"something"}', $data);
                }
            ));

        $this->object->addEvent(
            new Stream('test_stream'),
            ['event' => 'something']
        );
    }
}

