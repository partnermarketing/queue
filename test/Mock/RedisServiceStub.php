<?php

namespace Partnermarketing\Queue\Test\Mock;

use Partnermarketing\Queue\Service\RedisService;

/**
 * A stub which exists only to extend the RedisService to make it
 * accessible
 */
class RedisServiceStub extends RedisService
{
    /**
     * Just gets the connection for the tests
     */
    public function getConnection()
    {
        return $this->conn;
    }

    /**
     * Just gets the details for the tests
     */
    public function getDetails()
    {
        return $this->details;
    }
}
