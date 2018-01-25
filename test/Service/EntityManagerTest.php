<?php

namespace Partnermarketing\Queue\Test\Service;

use RuntimeException;
use Partnermarketing\Queue\Entity\Connection;
use Partnermarketing\Queue\Entity\Stream;
use Partnermarketing\Queue\Entity\Queue;
use Partnermarketing\Queue\Service\EntityConsumer;
use Partnermarketing\Queue\Service\EntityManager;
use Partnermarketing\Queue\Service\EventPublisher;
use Partnermarketing\Queue\Test\Mock\EntityManagerStub;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Tests that the methods in the EntityManger work as expected
 */
class EntityManagerTest extends EntityManagerTestHelper
{
    /**
     * Sets up a mocked version of the service for testing without the
     * constructor
     */
    private function setUpMockedObject() : void
    {
        $this->object = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->setProperty('type', 'entity');
    }

    /**
     * Runs basic setup tasks by setting the test mode and setups up the
     * reflector
     */
    public function setUp() : void
    {
        EntityManager::setTestMode();
        $this->reflect = new ReflectionClass(EntityManager::class);
    }

    /**
     * Tests that the constructor setups up properly
     */
    public function testConstructor() : void
    {
        $conn = new Connection();
        $object = new EntityManagerStub($conn, 'type');
        $this->object = $object->getEventPublisher();

        $this->assertSame($conn, $object->getDetails());
        $this->assertSame('type', $object->getType());
        $this->assertInstanceOf(
            EventPublisher::class,
            $this->object
        );

        $this->reflect = new ReflectionClass(EventPublisher::class);
        $this->assertSame(
            'type_test',
            $this->getProperty('stream')->getName()
        );
    }

    /**
     * Tests the getHash method
     */
    public function testGetHash() : void
    {
        $this->setUpMockedObject();
        $this->assertSame(
            'entity:123',
            $this->invokeMethod('getHash', ['123'])
        );
    }

    /**
     * Tests that getRequestStream returns the request stream for the
     * EntityManager type
     */
    public function testGetRequestStream() : void
    {
        $this->setUpMockedObject();
        $this->assertEquals(
            new Stream('entity_request'),
            $this->invokeMethod('getRequestStream', [])
        );
    }

    /**
     * Tests that getResponseStream returns the response stream for the
     * EntityManager type
     */
    public function testGetResponseStream()
    {
        $this->setUpMockedObject();
        $this->assertEquals(
            new Stream('entity_response'),
            $this->invokeMethod('getResponseStream', [])
        );
    }
}
