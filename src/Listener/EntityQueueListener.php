<?php

namespace Partnermarketing\Queue\Listener;

use Partnermarketing\Queue\Entity\Queue;
use Partnermarketing\Queue\Service\EntityConsumer;

/**
 * A specific listener which waits for a given entity to be advertised,
 * then gets it and calls the given EntityListener
 */
class EntityQueueListener extends AbstractQueueListener
{
    /**
     * The id of the entity we're listening for
     *
     * @var string
     */
    private $id;

    /**
     * The entity consumer that manages this operation
     *
     * @var EntityConsumer
     */
    private $consumer;

    /**
     * The Entity Listener which should be called when the desired
     * entity comes in
     *
     * @var EntityListener
     */
    private $listener;

    /**
     * Constructs the handler with the queue info and a callback to
     * run when the execute method is called
     */
    public function __construct(
        EntityConsumer $consumer,
        $id,
        EntityListener $listener
    ) {
        parent::__construct($consumer->getQueue());

        $this->id = $id;
        $this->consumer = $consumer;
        $this->listener = $listener;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($event)
    {
        if ($event['uuid'] !== $this->id) {
            return;
        }

        $this->listener->withEntity(
            $this->consumer->getData($this->id)
        );
        $this->complete = true;
    }
}
