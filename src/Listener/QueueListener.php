<?php

namespace Partnermarketing\Queue\Listener;

use Partnermarketing\Queue\Entity\Queue;

/**
 * A service which handles events coming in on a queue
 */
interface QueueListener
{
    /**
     * Returns the Queue entity for information about this Queue
     * that this handles
     *
     * @return Queue
     */
    public function getQueue();

    /**
     * Returns if the Listener is finished
     *
     * The ListenerHandler will check this after each time the execute()
     * hook is called. If it has finished, the listener will be
     * removed automatically
     *
     * @return bool
     */
    public function isComplete();

    /**
     * Executes when an event comes in
     *
     * @param array $event
     * @return mixed Any return is acceptable
     */
    public function execute($event);
}
