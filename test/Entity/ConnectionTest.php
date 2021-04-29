<?php

namespace Partnermarketing\Queue\Test\Entity;

use PHPUnit\Framework\TestCase;
use Partnermarketing\Queue\Entity\Connection;

/**
 * Tests the connection
 */
class ConnectionTest extends TestCase
{
    /**
     * Tests that the default values are used correctly if none are
     * given to the constructor
     */
    public function testDefaults()
    {
        $connection = new Connection();
        $this->assertSame('redis', $connection->getHost());
        $this->assertSame(6379, $connection->getPort());
    }

    /**
     * Tests that given values are used if they are present
     */
    public function testGiven()
    {
        $connection = new Connection('server', 123);
        $this->assertSame('server', $connection->getHost());
        $this->assertSame(123, $connection->getPort());
    }

    /**
     * Tests that when the server name is not given but there is an
     * environment variable, it falls back to this
     */
    public function testEnvFallback()
    {
        putenv('REDIS_HOST=foobar');
        $connection = new Connection();
        $this->assertSame('foobar', $connection->getHost());

        // Unset it after the test
        putenv('REDIS_HOST');
    }
}
