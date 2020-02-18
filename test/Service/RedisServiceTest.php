<?php

namespace Partnermarketing\Queue\Test\Service;

use Partnermarketing\Queue\Entity\Connection;
use Partnermarketing\Queue\Service\RedisService;
use Partnermarketing\Queue\Test\Mock\RedisServiceStub;
use PHPUnit\Framework\TestCase;
use Predis\Client;
use Redis;

require_once __DIR__ . '/../Mock/Redis.php';

/**
 * Tests the basic functions of test mode
 */
class RedisServiceTest extends TestCase
{
    /**
     * Tests that test mode can be turned on
     */
    public function testSetTestModeTrue()
    {
        RedisService::setTestMode(true);

        $this->assertTrue(RedisService::inTestMode());
    }

    /**
     * Tests that test mode can be turned off
     */
    public function testSetModeFalse()
    {
        RedisService::setTestMode(false);

        $this->assertFalse(RedisService::inTestMode());
    }

    /**
     * Runs a test on the constructor, either with test mode enabled or
     * disabled
     *
     * @param boolean $testMode
     * @return ?Client The redis connection
     */
    private function startConstructorTest(bool $testMode) : ?Client
    {
        RedisService::setTestMode($testMode);

        $details = new Connection('host', 123);
        $redisService = new RedisServiceStub($details);

        $this->assertSame($details, $redisService->getDetails());

        return $redisService->getConnection();
    }

    /**
     * Tests that, when not in test mode, a redis connection is
     * created
     */
    public function testConstructor() : void
    {
        $connection = $this->startConstructorTest(false);
        $this->assertInstanceOf(Client::class, $connection);
        $this->assertSame('host', $connection->getConnection()->getParameters()->host);
        $this->assertSame(123, $connection->getConnection()->getParameters()->port);
    }

    /**
     * Tests that, when in test mode, a redis conneciton is not created
     */
    public function testConstructorInTestMode() : void
    {
        $this->assertNull($this->startConstructorTest(true));
    }
}
