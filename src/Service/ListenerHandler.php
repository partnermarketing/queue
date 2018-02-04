<?php

namespace Partnermarketing\Queue\Service;

use BadMethodCallException;
use Partnermarketing\Queue\Exception\NoListenersException;
use Partnermarketing\Queue\Exception\TimeoutException;
use Partnermarketing\Queue\Listener\QueueListener;
use Partnermarketing\Queue\Entity\Connection;
use Partnermarketing\Queue\Entity\Queue;

/**
 * A Service which listens for events on multiple queues and hands them
 * to the appropriate listener to be processed
 */
class ListenerHandler extends RedisService
{
    /**
     * The default ListenerHandler used for this session
     *
     * This is useful for the EntityConsumer which needs to hook into
     * the running ListenerHandler
     *
     * @var ListenerHandler
     */
    private static $default;

    /**
     * Set the default ListenerHandler for the application
     *
     * EntityConsumers will use this by default if no listenerhandler is
     * provided
     *
     * @param ListenerHandler $listenerHandler
     */
    public static function setDefault(ListenerHandler $listenerHandler)
    {
        static::$default = $listenerHandler;
    }

    /**
     * Gets the default ListenerHandler for the application
     *
     * @return ListenerHandler
     */
    public static function getDefault()
    {
        return static::$default;
    }

    /**
     * The list of listeners on this connection
     */
    private $listeners = [];

    /**
     * Constructs this Service, setting it as the default if it has not
     * yet been set
     *
     * @param Connection $details
     */
    public function __construct(Connection $details)
    {
        parent::__construct($details);

        if (!static::$default) {
            static::setDefault($this);
        }
    }

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
     * @param bool $returnOnTimeout If true, a timeout will just return
     * @throws TimeoutException If the connection timed out
     */
    public function listen($timeout = 0, $returnOnTimeout = false)
    {
        try {
            while (true) {
                $this->listenOnce($timeout);
            }
        } catch (TimeoutException $e) {
            if (!$returnOnTimeout) {
                throw $e;
            }
        } catch (NoListenersException $e) {
            return;
        }
    }

    /**
     * Listens for one event on any queue, handles it, then returns
     *
     * @param int $timeout The timeout to listen for, or 0 for forever
     * @return mixed The return of the executed method
     * @throws TimeoutException If the connection timed out
     */
    public function listenOnce($timeout = 0)
    {
        if (!count($this->listeners)) {
            throw new NoListenersException();
        }

        $event = $this->conn->brPop(
            array_keys($this->listeners),
            $timeout
        );

        if (!$event) {
            throw new TimeoutException('Timed out waiting for events');
        }

        return $this->listeners[$event[0]]->execute(
            json_decode($event[1], true)
        );
    }
}
