<?php

namespace Partnermarketing\Queue\Entity;

/**
 * Represents a queue of events from a stream
 */
class Queue
{
    /**
     * The id of the queue
     *
     * @var string
     */
    private $serviceId;

    /**
     * The stream that this is a member of
     *
     * @var Stream
     */
    private $stream;

    /**
     * Constructs this entity
     *
     * @var string $serviceId
     * @var Stream $stream
     */
    public function __construct($serviceId, Stream $stream)
    {
        $this->serviceId = $serviceId;
        $this->stream = $stream;
    }

    /**
     * Gets the Redis list that the service will listen on
     *
     * @return string
     */
    public function getList()
    {
        return $this->stream->getQueueSet() . ':' . $this->serviceId;
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
     * Gets the Stream that this is a member of
     *
     * @var Queue
     */
    public function getStream()
    {
        return $this->stream;
    }
}
