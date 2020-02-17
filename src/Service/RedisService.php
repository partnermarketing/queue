<?php

namespace Partnermarketing\Queue\Service;

use Partnermarketing\Queue\Entity\Connection;
use Redis;

/**
 * Services that extend this have a connection to a redis service
 */
abstract class RedisService
{
    /**
     * If set to true, other classes will not make connections to a
     * Redis server
     *
     * This is here so that downstream libraries can fully test their
     * code
     *
     * @var boolean
     */
    private static $testMode = false;

    /**
     * Returns if the library is in test mode
     *
     * @return boolean
     */
    public static function inTestMode()
    {
        return self::$testMode;
    }

    /**
     * Sets if the library should be in test mode
     *
     * @param boolean $mode
     */
    public static function setTestMode($mode = true)
    {
        self::$testMode = $mode;
    }

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
     *
     * @param Connection $details
     */
    public function __construct(Connection $details)
    {
        $this->details = $details;

        if (!self::inTestMode()) {
            $this->conn = new Redis();
            $this->conn->pconnect(
                $this->details->getHost(),
                $this->details->getPort()
            );
        }
    }
}
