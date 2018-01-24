<?php

namespace Partnermarketing\Queue\Traits;

use Partnermarketing\Queue\Entity\Connection;
use Partnermarketing\Queue\Service\RedisService;
use Redis;

/**
 * A trait for any service that has a persisent connection
 */
trait HasConnection
{
    /**
     * The persistent connection for this service
     *
     * @var Redis
     */
    protected $conn;

    /**
     * The details of the connection
     *
     * @var Connection
     */
    protected $details;

    /**
     * Saves the given details and opens a new persistent connection
     */
    public function __construct(Connection $details)
    {
        $this->details = $details;
        $this->conn = $this->createConnection(true);
    }

    /**
     * Creates a new connection, optionally asking phpredis to use a
     * persistent one
     *
     * @param boolean $persistent
     * @return Redis
     */
    protected function createConnection($persistent = false)
    {
        if (RedisService::inTestMode()) {
            return null;
        }

        $method = $persistent ? 'pconnect' : 'connect';

        $conn = new Redis();
        $conn->$method(
            $this->details->getHost(),
            $this->details->getPort()
        );

        return $conn;
    }
}
