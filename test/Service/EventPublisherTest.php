<?php

namespace Partnermarketing\Queue\Test\Service;

use Partnermarketing\Queue\Service\EventPublisher;
use Partnermarketing\Queue\Entity\Connection;
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
     * Reflector for the stream property
     *
     * @var ReflectionProperty
     */
    private $streamProperty;

    /**
     * Reflector for the details property
     *
     * @var ReflectionProperty
     */
    private $detailsProperty;

    /**
     * Setup the mock connection
     */
    private function setUpMockConnection()
    {
        $this->conn = $this->getMockBuilder(Redis::class)
            ->disableOriginalConstructor()
            ->setMethods(['sMembers', 'lPush', 'setTimeout'])
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
     * Sets up the commonly used items for each test
     */
    public function setUp(): void
    {
        $this->event = null;
        $this->reflect = new ReflectionClass(EventPublisher::class);
        $this->object = $this->reflect->newInstanceWithoutConstructor();

        $this->detailsProperty = $this->reflect->getProperty('details');
        $this->detailsProperty->setAccessible(true);

        $this->streamProperty = $this->reflect->getProperty('stream');
        $this->streamProperty->setAccessible(true);
        $this->streamProperty->setValue(
            $this->object,
            new Stream('test_stream')
        );
    }

    /**
     * Tests that the constructor sets the stream and calls its parent
     * constructor to set the connection details
     */
    public function testConstructor()
    {
        EventPublisher::setTestMode();

        $conn = new Connection();
        $stream = new Stream('test_stream');

        $object = new EventPublisher($conn, $stream);

        $this->assertSame(
            $conn,
            $this->detailsProperty->getValue($object)
        );
        $this->assertSame(
            $stream,
            $this->streamProperty->getValue($object)
        );
    }

    /**
     * Tests that getStreamQueues() queues the redis SET for the
     * stream and returns wrapped Queue objects
     */
    public function testGetStreamQueues()
    {
        $this->setUpMockConnection();
        $return = iterator_to_array($this->object->getStreamQueues());

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
        $this->setUpMockConnection();
        $self = $this;
        $this->conn->expects($this->once())
            ->method('lPush')
            ->will($this->returnCallback(
                function($list, $data) use ($self) {
                    $this->assertSame('test_stream:queues:test', $list);
                    $this->assertSame('{"event":"something"}', $data);
                }
            ));

        $this->object->addEvent(['event' => 'something']);
    }
}

