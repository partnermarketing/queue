<?php

namespace Partnermarketing\Queue\Listener;

use Partnermarketing\Queue\Entity\Queue;

/**
 * Handles Listeners by calling whatever callable was given to it in its
 * constructor
 */
class CallbackQueueListener extends AbstractQueueListener
{
    /**
     * The callback to use when execute is called
     *
     * @var callable
     */
    private $callback;

    /**
     * Constructs the handler with the queue info and a callback to
     * run when the execute method is called
     *
     * @param Queue $queue
     * @param $callback
     */
    public function __construct(Queue $queue, $callback)
    {
        parent::__construct($queue);

        $this->callback = $callback;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($event)
    {
        $cb = $this->callback;

        return $cb($event);
    }
}
