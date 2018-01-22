<?php

namespace Partnermarketing\Queue\Service;

use Partnermarketing\Queue\Traits\HasConnection;

/**
 * Services that extend this have a connection to a redis service
 */
abstract class RedisService
{
    use HasConnection;
}
