<?php

namespace Partnermarketing\Queue\Test\Service;

use RuntimeException;
use Partnermarketing\Queue\Entity\Stream;
use Partnermarketing\Queue\Entity\Queue;
use Partnermarketing\Queue\Service\EntityConsumer;
use Partnermarketing\Queue\Service\EventPublisher;
use ReflectionClass;

class EntityConsumerTest extends EntityManagerTest
{
    /**
     * The response queue the EntityConsumer listens on when waiting for
     * an entity
     *
     * @var Queue
     */
    private $queue;

    /**
     * Sets up the service being tested
     */
    public function setUp() : void
    {
        $this->reflect = new ReflectionClass(EntityConsumer::class);
        $this->object = $this->reflect->newInstanceWithoutConstructor();

        $this->conn = $this->getMockBuilder(Redis::class)
            ->disableOriginalConstructor()
            ->setMethods(['hMGet'])
            ->getMock();

        $this->setUpEventPublisher();

        $this->queue = new Queue('test', new Stream('entity_request'));
        $this->setProperty('queue', $this->queue);
        $this->setProperty('type', 'entity');
        $this->setProperty('eventPublisher', $this->eventPublisher);
        $this->setProperty('conn', $this->conn);
    }

    /**
     * Tests that getQueue() returns the queue object that was set
     */
    public function testGetQueue()
    {
        $this->assertSame($this->queue, $this->object->getQueue());
    }

    /**
     * Tests that execute() saves the uuid in the event
     */
    public function testExecute()
    {
        $this->object->execute(['uuid' => 'test']);

        $lastEntity = $this->reflect->getProperty('lastEntity');
        $lastEntity->setAccessible(true);

        $this->assertSame('test', $lastEntity->getValue($this->object));
    }

    /**
     * Tests that adverise() adds an event to the request stream
     */
    public function testAdvertise()
    {
        $this->expectAddEvent();
        $this->invokeMethod('advertise', ['123']);
    }

    /**
     * Sets up a test on the waitForEntity() tests
     *
     * @param object $count The PHPUnit number of times we expect
     *      listenOnce() to be called on the listenerHandler
     * @param ?string $lastEntity What to present the lastEntityt o
     */
    private function setUpWaitForEntityTest($count, ?string $lastEntity)
    {
        $listenerHandler = $this->getMockBuilder(ListenerHandler::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'registerListener',
                'deregisterListener',
                'listenOnce'
            ])
            ->getMock();

        $listenerHandler->expects($this->once())
            ->method('registerListener')
            ->with($this->object);

        $self = $this;
        $listen = $listenerHandler->expects($count)
            ->method('listenOnce')
            ->with(20)
            ->will($this->returnCallback(function() use ($self) {
                static $i = 0;

                if (++$i === 2) {
                    $self->setProperty('lastEntity', '123');
                }
            }));

        $listenerHandler->expects($this->once())
            ->method('deregisterListener')
            ->with($this->object);

        $this->setProperty('listenerHandler', $listenerHandler);
        $this->setProperty('lastEntity', $lastEntity);
    }

    /**
     * Tests that, when no lastEntity is set, the waitForEntity() method
     * correctly realises it has timed out and throws an exception
     */
    public function testWaitForEntityTimeout()
    {
        $this->setUpWaitForEntityTest($this->once(), null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Waiting for entity timed out');

        $this->invokeMethod('waitForEntity', ['123', 20]);
    }

    /**
     * Tests that, if the first time the listenOnce() method is used,
     * the wrong entity is returned, it tries again
     */
    public function testWaitForEntityTwoTries()
    {
        $this->setUpWaitForEntityTest($this->exactly(2), 'test2');

        $this->invokeMethod('waitForEntity', ['123', 20]);
    }

    /**
     * Tests that, if on the first time, the write entity is returned,
     * it immediately breaks
     */
    public function testWaitForEntitySuccess()
    {
        $this->setUpWaitForEntityTest($this->once(), '123');

        $this->invokeMethod('waitForEntity', ['123', 20]);
    }

    /**
     * Expects that a REDIS HMGET call will be made
     *
     * @param array $data The data to return on consecutive calls
     * @param object $count The PHPUnit number of times we expect it
     */
    private function expectHMGet(array $data, $count = null)
    {
        $this->conn->expects($count ?? $this->once())
            ->method('hMGet')
            ->with('entity:123', ['foo'])
            ->will($this->onConsecutiveCalls(...$data));
    }

    /**
     * Tests that getData() does a REDIS HMGET call and returns the
     * formatted data
     */
    public function testGetDataWithData()
    {
        $this->expectHMGet([['foo' => 'bar']]);
        $this->assertSame(
            ['foo' => 'bar'],
            $this->invokeMethod('getData', ['123', ['foo']])
        );
    }

    /**
     * Tests that getData() returns null if an empty array is returned
     */
    public function testGetDataWithoutData()
    {
        $this->expectHMGet([[]]);
        $this->assertNull(
            $this->invokeMethod('getData', ['123', ['foo']])
        );
    }

    /**
     * Tests that if there is data, getEntityValues returns this
     */
    public function testGetEntityValuesFirstTry()
    {
        $this->expectHMGet([['foo' => 'bar']]);

        $this->assertSame(
            ['foo' => 'bar'],
            $this->object->getEntityValues('123', ['foo'])
        );
    }

    /**
     * Tests that if there is no data, null is returned if request is
     * false
     */
    public function testGetEntityValuesNoRequest()
    {
        $this->expectHMGet([null]);
        $this->assertNull(
            $this->object->getEntityValues('123', ['foo'], false)
        );
    }

    /**
     * Tests that if no data is returned, it will request the entity
     */
    public function testGetEntityValuesRequest()
    {
        $this->expectHMGet([null, ['foo' => 'bar']], $this->exactly(2));
        $this->expectAddEvent();
        $this->setUpWaitForEntityTest($this->once(), '123');

        $this->object->setTimeout(20);

        $this->assertSame(
            ['foo' => 'bar'],
            $this->object->getEntityValues('123', ['foo'])
        );
    }

    /**
     * Tests that setTimeout sets the timeout as we would expect
     */
    public function testSetTimeout()
    {
        $this->object->setTimeout(100);

        $timeout = $this->reflect->getProperty('timeout');
        $timeout->setAccessible(true);
        $this->assertSame(100, $timeout->getValue($this->object));
    }

    /**
     * Tests that getEntityValue passes through to getEntityValues and
     * formats the response
     */
    public function testGetEntityValue()
    {
        $this->expectHMGet([['foo' => 'bar']]);
        $this->assertSame(
            'bar',
            $this->object->getEntityValue('123', 'foo')
        );
    }
}
