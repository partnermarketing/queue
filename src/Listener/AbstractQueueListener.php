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
    protected $queue;

    /**
     * If this listener has finished
     *
     * By default, the abstract queue listener will never mark itself as
     * complete, but to automatically deregister the listener just set
     * this as true
     *
     * @var bool
     */
    protected $complete = false;

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
    public function isComplete()
    {
        return $this->complete;
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
    abstract public function execute($event);
}
