<?php

namespace Partnermarketing\Queue\Service;

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
     */
    public function execute($event);
}
