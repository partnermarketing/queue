<?php

namespace Partnermarketing\Queue\Test\Mock;

/**
 * A Redis stub to test that it is instantiated properly
 */
class Redis
{
    /**
     * Saves the first argument given to pconnect()
     */
    public $host;

    /**
     * Saves the second argument given to pconnect()
     */
    public $port;

    /**
     * Mocks the pconnect() method by saving its values
     */
    public function pconnect($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
    }
}
