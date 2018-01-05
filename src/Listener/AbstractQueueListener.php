<?php

namespace Partnermarketing\Queue\Listener;

use Partnermarketing\Queue\Entity\Queue;

/**
 * The abstract implementation of the QueueListener
 *
 * This implements the getQueue() method by accepting one via a
 * constructor, but leaves the implementation of execute() to child
 * classes
 */
abstract class AbstractQueueListener implements QueueListener
{
    /**
     * Information about the queue this handles
     *
     * @var Queue
     */
    private $queue;

    /**
     * Constructs this handler
     *
     * @param Queue $queue
     */
    public function __construct(Queue $queue)
    {
        $this->queue = $queue;
    }

    /**
     * {@inheritDoc}
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * {@inheritDoc}
     */
    abstract function execute($event);
}
