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
     * Executes when an event comes in
     *
     * @param array $event
     * @return mixed Any return is acceptable
     */
    public function execute($event);
}
