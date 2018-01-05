<?php

namespace Partnermarketing\Queue\Test\Service;

use Partnermarketing\Queue\Service\EntityProvider;
use PHPUnit\Framework\TestCase;
use Redis;
use ReflectionClass;
use ReflectionProperty;

/**
 */
class EntityProviderTest extends TestCase
{
    /**
     * The mock Redis connection that is injected into the handler
     *
     * @var Redis
     */
    private $conn;

    /**
     * The EntityProvider we are testing
     *
     * @var EntityProvider
     */
    private $object;

    /**
     * A reflector for the EntityProvider so we can interact with its
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
        $this->reflect = new ReflectionClass(EntityProvider::class);
        $this->object = $this->reflect->newInstanceWithoutConstructor();

        $this->conn = $this->getMockBuilder(Redis::class)
            ->disableOriginalConstructor()
            ->setMethods(['hMSet', 'publish'])
            ->getMock();

        $self = $this;
        $this->conn->expects($this->once())
            ->method('hMSet')
            ->will($this->returnCallback(
                function($hash, $data) use ($self) {
                    $this->assertSame('entity:123', $hash);
                    $this->assertEquals(
                        [
                            'uuid' => '123',
                            'data' => '1'
                        ],
                        $data
                    );
                }
            ));

        $conn = $this->reflect->getProperty('conn');
        $conn->setAccessible(true);
        $conn->setValue($this->object, $this->conn);

        $type = $this->reflect->getProperty('type');
        $type->setAccessible(true);
        $type->setValue($this->object, 'entity');

        $id = $this->reflect->getProperty('id');
        $id->setAccessible(true);
        $id->setValue($this->object, '123');
    }

    /**
     * Expects the publish() method to be called the given number of
     * times
     *
     * @param int $times
     * @param Object $will The return values
     */
    private function expectPublish($times, $will)
    {
        $this->conn->expects($this->exactly($times))
            ->method('publish')
            ->will($will);
    }

    /**
     * Calls save on the object with the dud data
     *
     * @param boolean $retry
     */
    private function runSave($retry)
    {
        $this->object->save(['data' => '1'], $retry);
    }

    /**
     * Tests that when we don't say retry the publish() method is only
     * called once, even on a failure
     */
    public function testSaveWithoutRetryAndAFail()
    {
        $this->expectPublish(1, $this->onConsecutiveCalls(0));
        $this->runSave(false);
    }

    /**
     * Tests that when we don't say retry the publish() method is only
     * called once without failure
     */
    public function testSaveWithoutRetryAndNotFail()
    {
        $this->expectPublish(1, $this->onConsecutiveCalls(1));
        $this->runSave(false);
    }

    /**
     * Tests that when we say retry the publish() method is called twice
     * when the first fails
     */
    public function testSaveWithRetryAndAFail()
    {
        $this->expectPublish(2, $this->onConsecutiveCalls(0, 1));
        $this->runSave(true);
    }

    /**
     * Tests that when we say retry the publish() method is only called
     * once in the event that the first does not fail
     */
    public function testSaveWithRetryAndNoFail()
    {
        $this->expectPublish(1, $this->onConsecutiveCalls(1));
        $this->runSave(true);
    }
}
