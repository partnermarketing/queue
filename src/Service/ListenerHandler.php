<?php

namespace Partnermarketing\Queue\Service;

use BadMethodCallException;
use Partnermarketing\Queue\Exception\TimeoutException;
use Partnermarketing\Queue\Listener\QueueListener;
use Partnermarketing\Queue\Entity\Queue;

/**
 * A Service which listens for events on multiple queues and hands them
 * to the appropriate listener to be processed
 */
class ListenerHandler extends RedisService
{
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
     * Deletes a listener based on a queue
     *
     * @param Queue $queue
     * @param boolean $force Does not throw exception if not registered
     * @throws BadMethodCallException If the queue isn't registered
     */
    public function deregisterQueue(Queue $queue, $force = false)
    {
        if (!isset($this->listeners[$queue->getList()]) && !$force) {
            throw new BadMethodCallException(
                'Queue ' . $queue->getName() . ' is not registered'
            );
        }

        unset($this->listeners[$queue->getList()]);

        $this->conn->sRem(
            $queue->getStream()->getQueueSet(),
            $queue->getName()
        );
    }

    /**
     * Deletes a listener
     *
     * @param QueueListener
     * @throws BadMethodCallException If the queue isn't registered
     */
    public function deregisterListener(QueueListener $listener)
    {
        $this->deregisterQueue($listener->getQueue());
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

        if (!$event) {
            throw new TimeoutException('Timed out waiting for events');
        }

        $this->listeners[$event[0]]->execute(
            json_decode($event[1], true)
        );
    }
}
