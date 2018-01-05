<?php

namespace Partnermarketing\Queue\Service;

use Partnermarketing\Queue\Traits\HasConnection;
use Partnermarketing\Queue\Listener\QueueListener;

/**
 * A Service which listens for events on multiple queues and hands them
 * to the appropriate listener to be processed
 */
class ListenerHandler
{
    use HasConnection;

    /**
     * The list of listeners on this connection
     */
    private $listeners = [];

    /**
     * Register a new Listener
     *
     * @param QueueListener $listener
     */
    public function registerListener(QueueListener $listener)
    {
        $queue = $listener->getQueue();
        $this->conn->sAdd(
            $queue->getStream()->getQueueSet(),
            $queue->getName()
        );
        $this->listeners[$queue->getList()] = $listener;
    }

    /**
     * Enters a listen loop, handling each request as they come in, then
     * listening again
     *
     * @param int $timeout The timeout to listen for, or 0 for forever
     */
    public function listen($timeout = 0)
    {
        while (true) {
            $this->listenOnce($timeout);
        }
    }

    /**
     * Listens for one event on any queue, handles it, then returns
     *
     * @param int $timeout The timeout to listen for, or 0 for forever
     */
    public function listenOnce($timeout = 0)
    {
        $event = $this->conn->brPop(
            array_keys($this->listeners),
            $timeout
        );

        $this->listeners[$event[0]]->execute(
            json_decode($event[1], true)
        );
    }
}
