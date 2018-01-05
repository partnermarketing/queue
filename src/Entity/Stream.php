<?php

namespace Partnermarketing\Queue\Entity;

/**
 * Represents a stream of data events which can be split into multiple
 * queues for different listeners
 */
class Stream
{
    /**
     * The name of this stream
     *
     * @var string
     */
    private $name;

    /**
     * Sets up this entity
     *
     * @param string $name The name of this stream
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Gets this stream's name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the Redis set of all this stream's queues
     *
     * @return string
     */
    public function getQueueSet()
    {
        return $this->name . ':queues';
    }
}
