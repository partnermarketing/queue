<?php

namespace Partnermarketing\Queue\Test\Service;

use Partnermarketing\Queue\Listener\CallbackEntityListener;
use RuntimeException;
use Partnermarketing\Queue\Entity\Connection;
use Partnermarketing\Queue\Entity\Stream;
use Partnermarketing\Queue\Entity\Queue;
use Partnermarketing\Queue\Listener\EntityListener;
use Partnermarketing\Queue\Listener\EntityQueueListener;
use Partnermarketing\Queue\Exception\TimeoutException;
use Partnermarketing\Queue\Service\ListenerHandler;
use Partnermarketing\Queue\Service\EntityConsumer;
use ReflectionClass;

class EntityConsumerTest extends EntityManagerTestHelper
{
    /**
     * The response queue the EntityConsumer listens on when waiting for
     * an entity
     *
     * @var Queue
     */
    private $queue;

    /**
     * The mock ListenerHandler used by the entity consumer
     *
     * @var ListenerHandler
     */
    private $listener;

    /**
     * Sets up the service being tested
     */
    public function setUp() : void
    {
        $this->reflect = new ReflectionClass(EntityConsumer::class);
        $this->object = $this->reflect->newInstanceWithoutConstructor();

        $this->conn = $this->getMockBuilder(Redis::class)
            ->disableOriginalConstructor()
            ->setMethods(['hGetAll'])
            ->getMock();

        $this->setUpEventPublisher();
        $this->setObjectProperties();
    }

    private function setObjectProperties()
    {
        $this->queue = new Queue('test', new Stream('entity_request'));
        $this->setProperty('queue', $this->queue);
        $this->setProperty('type', 'entity');
        $this->setProperty('eventPublisher', $this->eventPublisher);
        $this->setProperty('conn', $this->conn);
    }

    /**
     * Tests that when the constructor is given a listenerhandler, that
     * is used and the rest of the object is constructed
     */
    public function testConstructorWithListenerHandler()
    {
        $conn = new Connection();
        $handler = new ListenerHandler($conn);
        $this->object = new EntityConsumer($conn, 'type', $handler);

        $this->assertSame(
            'type_response',
            $this->object->getQueue()->getStream()->getName()
        );
        $this->assertSame(
            1,
            preg_match(
                '/^[0-9a-z]{13}$/',
                $this->object->getQueue()->getName()
            )
        );
        $this->assertSame(
            $handler,
            $this->getProperty('listenerHandler')
        );
    }

    /**
     * Tests that when the constructor is not given a listener handler,
     * it uses the default
     */
    public function testConstructorWithoutListnerHandler()
    {
        $conn = new Connection();
        $handler = new ListenerHandler($conn);
        ListenerHandler::setDefault($handler);
        $this->object = new EntityConsumer($conn, 'type');

        $this->assertSame(
            $handler,
            $this->getProperty('listenerHandler')
        );
    }

    /**
     * Tests that getQueue() returns the queue object that was set
     */
    public function testGetQueue()
    {
        $this->assertSame($this->queue, $this->object->getQueue());
    }

    /**
     * Tests that buildQueue() returns new queue and set it to object
     */
    public function testBuildQueue()
    {
        $queue1 = $this->object->getQueue()->getName();
        $this->object->buildQueue();
        $this->assertNotEquals($this->object->getQueue()->getName(), $queue1);

        $queue2 = $this->queue->getName();
        $this->object->buildQueue();
        $this->assertNotEquals($this->object->getQueue()->getName(), $queue2);
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
     * Expects that a REDIS HGETALL call will be made
     *
     * @param array $data The data to return on consecutive calls
     * @param object $count The PHPUnit number of times we expect it
     */
    private function expectHGetAll(array $data, $count = null)
    {
        $this->conn->expects($count ?? $this->once())
            ->method('hGetAll')
            ->with('entity:123')
            ->will($this->onConsecutiveCalls(...$data));
    }

    /**
     * Tests that getData() does a REDIS HMGET call and returns the
     * formatted data
     */
    public function testGetDataWithData()
    {
        $this->expectHGetAll([['foo' => 'bar']]);
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
        $this->expectHGetAll([[]]);
        $this->assertNull(
            $this->invokeMethod('getData', ['123', ['foo']])
        );
    }

    /**
     * Gets a mock EntityListener for the EntityConsumer
     *
     * @return EntityListener
     */
    private function getMockEntityListener()
    {
        return $this->getMockBuilder(EntityListener::class)
            ->disableOriginalConstructor()
            ->setMethods(['withEntity'])
            ->getMock();
    }

    /**
     * Tests that if the data exists, it just calls the withEntity() on
     * it straight away
     */
    public function testWithEntityValuesExists()
    {
        $this->expectHGetAll([['foo' => 'bar']]);
        $listener = $this->getMockEntityListener();
        $listener->expects($this->once())
            ->method('withEntity')
            ->with(['foo' => 'bar']);

        $this->object->withEntityValues('123', $listener);
    }

    /**
     * Tests that if the data does not exist, it asks for it, then sets
     * up a listener for it
     */
    public function testWithEntityValuesNotExists()
    {
        $this->expectAddEvent();
        $this->expectHGetAll([[]]);

        $handler = $this->getMockBuilder(ListenerHandler::class)
            ->disableOriginalConstructor()
            ->setMethods(['registerListener'])
            ->getMock();

        $handler->expects($this->once())
            ->method('registerListener')
            ->will($this->returnCallback([
                $this,
                'withEntityValuesRegisterCallback'
            ]));

        $this->setProperty('listenerHandler', $handler);
        $this->listener = $this->getMockEntityListener();

        $this->object->withEntityValues('123', $this->listener);
    }

    /**
     * The callback used by the mock ListenerHandler to test when
     * registerListener is called
     *
     * @param $arg The argument passed to it by the EntityConsumer
     */
    public function withEntityValuesRegisterCallback($arg)
    {
        $consumer = $this->object;
        $this->assertInstanceOf(EntityQueueListener::class, $arg);
        $this->object = $arg;
        $this->reflect =
            new ReflectionClass(EntityQueueListener::class);

        $this->assertSame($consumer, $this->getProperty('consumer'));
        $this->assertSame('123', $this->getProperty('id'));
        $this->assertSame(
            $this->listener,
            $this->getProperty('listener')
        );
    }

    /**
     * Tests that if the data does not exist, it asks for it, then sets
     * up a listener for it
     */
    public function testWithEntityValuesNotExistsCallBuildQueue()
    {
        $this->object = $this->createPartialMock(EntityConsumer::class, ['buildQueue']);
        $this->object->expects($this->once())->method('buildQueue');
        $this->setObjectProperties();

        $this->expectAddEvent();
        $this->expectHGetAll([[]]);

        $handler = $this->getMockBuilder(ListenerHandler::class)
            ->disableOriginalConstructor()
            ->setMethods(['registerListener'])
            ->getMock();

        $handler->expects($this->once())
            ->method('registerListener')
            ->will($this->returnCallback([
                $this,
                'withEntityValuesRegisterCallback'
            ]));

        $this->setProperty('listenerHandler', $handler);
        $this->listener = $this->getMockEntityListener();

        $this->object->withEntityValues('123', $this->listener);
    }

    public function testWithEntitiesValues()
    {
        $this->object = $this->createPartialMock(EntityConsumer::class, ['withEntityValues']);
        $this->object->expects($this->at(0))
            ->method('withEntityValues')
            ->with($this->equalTo(1), $this->callback(function($callback) {
                    $callback->withEntity('result1');
                    return $callback instanceof CallbackEntityListener;
            }));
        $this->object->expects($this->at(1))
            ->method('withEntityValues')
            ->with($this->equalTo(2), $this->callback(function($callback) {
                $callback->withEntity('result2');
                return $callback instanceof CallbackEntityListener;
            }));
        $this->object->expects($this->at(2))
            ->method('withEntityValues')
            ->with($this->equalTo(3), $this->callback(function($callback) {
                $callback->withEntity('result3');
                return $callback instanceof CallbackEntityListener;
            }));
        $this->setObjectProperties();

        $this->listener = $this->getMockEntityListener();
        $this->listener->expects($this->once())
            ->method('withEntity')
            ->with(['result1', 'result2', 'result3']);

        $this->object->withEntitiesValues([1, 2, 3], $this->listener);
    }
}
