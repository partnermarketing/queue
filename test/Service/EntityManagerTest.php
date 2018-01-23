<?php

namespace Partnermarketing\Queue\Test\Service;

use RuntimeException;
use Partnermarketing\Queue\Entity\Stream;
use Partnermarketing\Queue\Entity\Queue;
use Partnermarketing\Queue\Service\EntityConsumer;
use Partnermarketing\Queue\Service\EventPublisher;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class EntityManagerTest extends TestCase
{
    /**
     * A reflector for the EntityConsumer
     *
     * @var ReflectionClass
     */
    protected $reflect;

    /**
     * The instance of the EntityConsumer service that we are testing
     *
     * @var EntityConsumer
     */
    protected $object;

    /**
     * The response queue the EntityConsumer listens on when waiting for
     * an entity
     *
     * @var Queue
     */
    private $queue;

    /**
     * A mock eventPublisher to be used by the service
     *
     * @var EventPublisher
     */
    protected $eventPublisher;

    /**
     * Sets a property on the service, even if it is private
     *
     * @param string $property The property to set
     * @param mixed $value The value to set
     */
    protected function setProperty(string $property, $value) : void
    {
        $prop = $this->reflect->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($this->object, $value);
    }

    /**
     * Invokes a method on the service with the given arguments, even if
     * it is priviate, and returns its response
     *
     * @param string $methodName The method to test
     * @param array $args The arguments to give to the method
     * @return mixed The return of the method call
     */
    protected function invokeMethod(string $methodName, array $args)
    {
        $method = $this->reflect->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($this->object, $args);
    }

    protected function setUpEventPublisher()
    {
        $this->eventPublisher =
            $this->getMockBuilder(EventPublisher::class)
                ->disableOriginalConstructor()
                ->setMethods(['addEvent'])
                ->getMock();
        $this->setProperty('eventPublisher', $this->eventPublisher);
    }

    /**
     * Expects that the service will add an event to the request queue
     */
    protected function expectAddEvent()
    {
        $this->eventPublisher->expects($this->once())
            ->method('addEvent')
            ->with(['uuid' => '123']);
    }
}
