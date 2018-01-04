<?php

namespace Partnermarketing\Queue\Entity;

/**
 * Represents something that listens for events on a queue
 */
class Listener
{
    /**
     * The id of the service that listens
     *
     * @var string
     */
    private $serviceId;

    /**
     * The queue that this listens on
     *
     * @var Queue
     */
    private $queue;

    /**
     * Constructs this entity
     *
     * @var string $serviceId
     * @var Queue $queue
     */
    public function __construct($serviceId, Queue $queue)
    {
        $this->serviceId = $serviceId;
        $this->queue = $queue;
    }

    /**
     * Gets the Redis list that the service will listen on
     *
     * @return string
     */
    public function getList()
    {
        return $this->queue->getListenerSet() . ':' . $this->serviceId;
    }

    /**
     * Gets the id of the service that will listen
     *
     * @return string
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * Gets the Queue that the service will listen to
     *
     * @var Queue
     */
    public function getQueue()
    {
        return $this->queue;
    }
}
