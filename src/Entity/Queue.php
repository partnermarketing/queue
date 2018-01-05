<?php

namespace Partnermarketing\Queue\Entity;

/**
 * Represents a queue of events from a stream
 */
class Queue
{
    /**
     * The name of the queue
     *
     * @var string
     */
    private $name;

    /**
     * The stream that this is a member of
     *
     * @var Stream
     */
    private $stream;

    /**
     * Constructs this entity
     *
     * @var string $name
     * @var Stream $stream
     */
    public function __construct($name, Stream $stream)
    {
        $this->name = $name;
        $this->stream = $stream;
    }

    /**
     * Gets the Redis list that the service will listen on
     *
     * @return string
     */
    public function getList()
    {
        return $this->stream->getQueueSet() . ':' . $this->name;
    }

    /**
     * Gets the name of the queue
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
