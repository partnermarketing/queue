<?php

namespace Partnermarketing\Queue\Test\Listener;

use Partnermarketing\Queue\Listener\EntityQueueListener;
use Partnermarketing\Queue\Listener\EntityListener;
use Partnermarketing\Queue\Entity\Stream;
use Partnermarketing\Queue\Entity\Queue;
use Partnermarketing\Queue\Service\EntityConsumer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

/**
 * Tests that the EntityQueueListener acts as we expect it to
 */
class EntityQueueListenerTest extends TestCase
{
    /**
     * The reflector for the service
     *
     * @var ReflectionClass
     */
    private $reflect;

    /**
     * The reflector for the service's id
     *
     * @var ReflectionProperty
     */
    private $id;

    /**
     * The reflector for the service's consumer
     *
     * @var ReflectionProperty
     */
    private $consumer;

    /**
     * The reflector for the service's listener
     *
     * @var EntityListener
     */
    private $listener;

    /**
     * A queue for the mocked Entity Consumer
     *
     * @var Queue
     */
    private $queue;

    /**
     * Sets up the tests
     */
    public function setUp(): void
    {
        $this->reflect =
            new ReflectionClass(EntityQueueListener::class);

        $this->id = $this->reflect->getProperty('id');
        $this->id->setAccessible(true);

        $this->consumer = $this->reflect->getProperty('consumer');
        $this->consumer->setAccessible(true);

        $this->listener = $this->reflect->getProperty('listener');
        $this->listener->setAccessible(true);

        $this->queue = new Queue('123', new Stream('123'));
    }

    /**
     * Gets a mock EntityConsumer
     *
     * @return EntityConsumer
     */
    private function getMockConsumer()
    {
        $consumer = $this->getMockBuilder(EntityConsumer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQueue', 'getData'])
            ->getMock();

        $consumer->expects($this->once())
            ->method('getQueue')
            ->willReturn($this->queue);

        return $consumer;

    }

    /**
     * Gets a mock EntityListener
     *
     * @return EntityListener
     */
    private function getMockListener()
    {
        return $this->getMockBuilder(EntityListener::class)
            ->disableOriginalConstructor()
            ->setMethods(['withEntity'])
            ->getMock();
    }

    /**
     * Tests that the constructor sets the values as expected
     */
    public function testConstructor()
    {
        $consumer = $this->getMockConsumer();
        $listener = $this->getMockListener();

        $object = new EntityQueueListener(
            $consumer,
            '123',
            $listener
        );

        $this->assertSame(
            $consumer,
            $this->consumer->getValue($object)
        );
        $this->assertSame(
            '123',
            $this->id->getValue($object)
        );
        $this->assertSame(
            $listener,
            $this->listener->getValue($object)
        );
        $this->assertSame(
            $this->queue,
            $object->getQueue()
        );
    }

    /**
     * Tests that execute just returns if the given uuid does not match
     * what it expects
     */
    public function testExecuteWrongUuid()
    {
        $object = $this->reflect->newInstanceWithoutConstructor();

        $this->id->setValue($object, '123');

        $object->execute(['uuid' => '456']);

        $this->assertFalse($object->isComplete());
    }

    /**
     * Tests that execute() gets the entity and calls the entity
     * listener when the uuids match
     */
    public function testExecute()
    {
        $consumer = $this->getMockConsumer();
        $listener = $this->getMockListener();

        $object = new EntityQueueListener(
            $consumer,
            '123',
            $listener
        );

        $listener->expects($this->once())
            ->method('withEntity')
            ->with(['uuid' => '123', 'foo' => 'bar']);

        $consumer->expects($this->once())
            ->method('getData')
            ->with('123')
            ->willReturn(['uuid' => '123', 'foo' => 'bar']);

        $object->execute(['uuid' => '123']);

        $this->assertTrue($object->isComplete());
    }
}
