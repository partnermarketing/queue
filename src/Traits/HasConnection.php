<?php

namespace Partnermarketing\Queue\Traits;

use Partnermarketing\Queue\Entity\Connection;
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
    private $conn;

    /**
     * The details of the connection
     *
     * @var Connection
     */
    private $details;

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
    private function createConnection($persistent = false)
    {
        $method = $persistent ? 'pconnect' : 'connect';

        $conn = new Redis();
        $conn->$method(
            $this->details->getHost(),
            $this->details->getPort()
        );

        return $conn;
    }
}
