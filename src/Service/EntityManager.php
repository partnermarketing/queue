<?php

namespace Partnermarketing\Queue\Service;

use Partnermarketing\Queue\Entity\Connection;
use Partnermarketing\Queue\Entity\Stream;

/**
 * An abstract service that is able to work with entities
 */
abstract class EntityManager extends RedisService
{
    /**
     * The type of this entity
     *
     * @var string
     */
    protected $type;

    /**
     * The event publisher to use for events
     *
     * @var EventPublisher
     */
    protected $eventPublisher;

    /**
     * Sets up a new entity manager with the connection details, the
     * entity type, and its id
     *
     * @param Connection $details
     * @param string $type
     * @param string $id
     */
    public function __construct(Connection $details, $type)
    {
        parent::__construct($details);

        $this->type = $type;
        $this->eventPublisher = new EventPublisher(
            $details,
            $this->getStreamOfType(static::TYPE)
        );
    }

    /**
     * Gets a stream of the given type
     *
     * Should only be used internally as a helper method for the nicer
     * wrapper methods and the constructor
     *
     * @param string $type
     * @see getRequestStream()
     * @see getResponseStream()
     * @return Stream
     */
    private function getStreamOfType($type)
    {
        return new Stream($this->type . '_' . $type);
    }

    /**
     * Gets the request stream for this entity
     *
     * @return Stream
     */
    protected function getRequestStream()
    {
        return $this->getStreamOfType('request');
    }

    /**
     * Gets the response stream for this entity
     *
     * @return Stream
     */
    protected function getResponseStream()
    {
        return $this->getStreamOfType('response');
    }

    /**
     * Gets the redis hash used by the entity
     *
     * @param string $id
     * @return string
     */
    protected function getHash($id)
    {
        return $this->type . ':' . $id;
    }

    /**
     * Publishes an event to service's stream
     *
     * @param string $id The entity to request
     */
    protected function advertise($id)
    {
        $this->eventPublisher->addEvent(['uuid' => $id]);
    }
}
