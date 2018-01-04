<?php

namespace Partnermarketing\Queue\Entity;

/**
 * Represents the details of a connection to a redis server
 */
class Connection
{
    /**
     * The host of the redis server
     *
     * @var string
     */
    private $host;

    /**
     * The port of the redis server
     *
     * @var string
     */
    private $port;

    /**
     * Constructs the details, using default values if not given
     *
     * @param string $host
     * @param int $port
     */
    public function __construct($host = 'redis', $port = 6379)
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Gets the redis server's hostname
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Gets the redis server's port
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }
}
