<?php

namespace Partnermarketing\Queue\Service;

use RuntimeException;
use Partnermarketing\Queue\Entity\Connection;
use Partnermarketing\Queue\Entity\Stream;
use Partnermarketing\Queue\Entity\Queue;
use Partnermarketing\Queue\Listener\QueueListener;

/**
 * A service that is able to read and request entity data from redis
 */
class EntityConsumer extends EntityManager implements QueueListener
{
    /**
     * The type of Stream that this sends event to
     *
     * @var string
     */
    const TYPE = 'request';

    /**
     * The response queue this can listen to
     *
     * @var Queue
     */
    private $queue;

    /**
     * The id of the last entity returned
     *
     * @var string
     */
    private $lastEntity;

    /**
     * The listenerHandler used when waiting for entity notifications
     *
     * @var ListenerHandler
     */
    private $listenerHandler;

    /**
     * The default timeout to wait for entities
     *
     * @var int
     */
    private $timeout = 10;

    public function __construct(Connection $details, $type)
    {
        parent::__construct($details, $type);

        $this->listenerHandler = new ListenerHandler($details);
        $this->queue = new Queue(uniqid(), $this->getResponseStream());
    }

    /**
     * Sets the timeout to be used when waiting for entities
     *
     * @var int $timeout
     * @return EntityConsumer $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
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
    public function execute($event)
    {
        $this->lastEntity = $event['uuid'];
    }


    /**
     * Blocks until it recieves an advertisment that the entity it wants
     * has been created
     *
     * @param string $id The entity to wait for
     * @param int $timeout The time to wait
     */
    private function waitForEntity($id, $timeout = 10)
    {
        $this->listenerHandler->registerListener($this);

        while (true) {
            $this->listenerHandler->listenOnce($timeout);

            if (!$this->lastEntity) {
                $this->listenerHandler->deregisterListener($this);
                throw new RuntimeException(
                    'Waiting for entity timed out'
                );
            } elseif ($this->lastEntity === $id) {
                $this->listenerHandler->deregisterListener($this);
                break;
            } else {
                $this->lastEntity = null;
            }
        }
    }

    /**
     * Gets data from the redis hash
     *
     * @param string $id
     * @param array $items
     * @return array The requested data
     */
    private function getData($id, $items)
    {
        $data = $this->conn->hMGet($this->getHash($id), $items);

        return !count($data) ? null : $data;
    }

    /**
     * Tries to load a single data value for the entity, optionally
     * requsting it if it does not already exist
     *
     * @param string $id The id of the entity to get
     * @param string $item The field to get
     * @param boolean $request If to request it if not present
     * @return mixed The value
     * @see getEntityValues()
     */
    public function getEntityValue($id, $item, $request = true)
    {
        $data = $this->getEntityValues($id, [$item], $request);

        return $data[$item];
    }

    /**
     * Tries to load the data for the given entity, optionally
     * requesting if it does not already exist
     *
     * @param string $id The id of the entity to get
     * @param array $items The items of the entity to get
     * @param boolean $request If to request it if not present
     * @return array The requested data
     */
    public function getEntityValues($id, $items, $request = true)
    {
        $data = $this->getData($id, $items);

        if (!$request || $data) {
            return $data;
        }

        $this->advertise($id);
        $this->waitForEntity($id, $this->timeout);

        return $this->getData($id, $items);
    }
}
