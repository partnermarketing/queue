<?php

namespace Partnermarketing\Queue\Service;

use Partnermarketing\Queue\Traits\HasConnection;

/**
 * Services that extend this have a connection to a redis service
 */
abstract class RedisService
{
    use HasConnection;

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
}
