<?php

namespace Partnermarketing\Queue\Test\Service;

use InvalidArgumentException;
use Partnermarketing\Queue\Service\EntityProvider;
use Redis;
use ReflectionClass;
use ReflectionProperty;

/**
 * Tests that the EntityProvider service works as expected
 */
class EntityProviderTest extends EntityManagerTestHelper
{
    /**
     * The mock Redis connection that is injected into the handler
     *
     * @var Redis
     */
    private $conn;

    /**
     * Sets up the commonly used items for each test
     */
    public function setUp(): void
    {
        $this->reflect = new ReflectionClass(EntityProvider::class);
        $this->object = $this->reflect->newInstanceWithoutConstructor();

        $this->conn = $this->getMockBuilder(Redis::class)
            ->disableOriginalConstructor()
            ->setMethods(['hMSet', 'publish', 'expire'])
            ->getMock();

        $this->setUpEventPublisher();

        $conn = $this->reflect->getProperty('conn');
        $conn->setAccessible(true);
        $conn->setValue($this->object, $this->conn);

        $type = $this->reflect->getProperty('type');
        $type->setAccessible(true);
        $type->setValue($this->object, 'entity');
    }

    /**
     * Expects that a REDIS HMSET call is made with the appropriate data
     */
    private function expectHMSet()
    {
        $this->conn->expects($this->once())
            ->method('hMSet')
            ->with('entity:123', ['uuid' => '123', 'data' => 1]);

        $this->conn->expects($this->once())
            ->method('expire')
            ->with('entity:123', 600);
    }

    /**
     * Calls save on the object with the dud data
     *
     * @param boolean $retry
     */
    private function runSave($advertise)
    {
        $this->object->save(['uuid' => '123', 'data' => 1], $advertise);
    }

    /**
     * Tests that if you try to save data without a uuid, an exception
     * is thrown
     */
    public function testSaveWithoutUuidThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provided data has no uuid');

        $this->object->save(['data' => 1]);
    }

    /**
     * Tests that, when not adviertising, the entity is saved and
     * nothing more
     */
    public function testSaveWithoutAdvertising()
    {
        $this->expectHMSet();
        $this->runSave(false);
    }

    /**
     * Tests that, when adviertising, the event is advertised
     */
    public function testSaveWithAdvertising()
    {
        $this->expectHMSet();
        $this->expectAddEvent();
        $this->runSave(true);
    }
}
