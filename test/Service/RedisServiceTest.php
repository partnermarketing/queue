<?php

namespace Partnermarketing\Queue\Test\Service;

use Partnermarketing\Queue\Service\RedisService;
use PHPUnit\Framework\TestCase;

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
}
