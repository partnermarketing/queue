<?php

namespace Partnermarketing\Queue\Service;

use Partnermarketing\Queue\Listener\CallbackEntityListener;
use Partnermarketing\Queue\Entity\Connection;
use Partnermarketing\Queue\Entity\Queue;
use Partnermarketing\Queue\Listener\EntityListener;
use Partnermarketing\Queue\Listener\EntityQueueListener;

/**
 * A service that is able to read and request entity data from redis
 */
class EntityConsumer extends EntityManager
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
     * The listenerHandler used when waiting for entity notifications
     *
     * @var ListenerHandler
     */
    private $listenerHandler;

    public function __construct(
        Connection $details,
        $type,
        ListenerHandler $listenerHandler = null
    ) {
        parent::__construct($details, $type);

        $this->buildQueue();

        if ($listenerHandler) {
            $this->listenerHandler = $listenerHandler;
        } else {
            $this->listenerHandler = ListenerHandler::getDefault();
        }
    }

    /**
     * Creates new unique queue.
     */
    public function buildQueue()
    {
        $this->queue = new Queue(uniqid('', true), $this->getResponseStream());
    }

    /**
     * {@inheritDoc}
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * Gets data from the redis hash
     *
     * @param string $id
     * @return array The requested data
     */
    public function getData($id)
    {
        $data = $this->conn->hGetAll($this->getHash($id));

        return !count($data) ? null : $data;
    }

    /**
     * Instructs the consumer that it wants to do something with the
     * given entity
     *
     * If the entity exists, the given listener is executed straight
     * away, otherwise it will be added to the listener handler
     *
     * @param string $id
     * @param EntityListener $listener
     */
    public function withEntityValues(
        $id,
        EntityListener $listener
    ) {
        $data = $this->getData($id);

        if ($data) {
            $listener->withEntity($data);
        } else {
            $this->buildQueue();
            $this->advertise($id);
            $this->listenerHandler->registerListener(
                new EntityQueueListener(
                    $this,
                    $id,
                    $listener
                )
            );
        }
    }

    /**
     * Instructs the consumer that it wants to do something with the
     * given entities ids
     *
     * If the entities exists, the given listener is executed straight
     * away, otherwise it will be added to the listener handler
     *
     * @param array $ids
     * @param EntityListener $listener
     */
    public function withEntitiesValues(
        array $ids,
        EntityListener $listener
    ) {
        $result = [];
        $batchListener = new CallbackEntityListener(static function($data) use ($ids, &$result, $listener) {
            $result[] = $data;

            if (count($ids) === count($result)) {
                $listener->withEntity($result);
            }
        });
        foreach ($ids as $id) {
            $this->withEntityValues($id, $batchListener);
        }
    }
}
