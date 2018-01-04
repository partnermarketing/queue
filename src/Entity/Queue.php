<?php

namespace Partnermarketing\Queue\Entity;

/**
 * Represents a queue of data which can have multiple streams going to
 * different listeners
 */
class Queue
{
    /**
     * The name of this queue
     *
     * @var string
     */
    private $name;

    /**
     * Sets up this entity
     *
     * @param string $name The name of this queue
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Gets this queue's name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the Redis set of all this queue's listeners
     *
     * @return string
     */
    public function getListenerSet()
    {
        return $this->name . ':listeners';
    }
}
