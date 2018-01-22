<?php

namespace Partnermarketing\Queue\Service;

use Partnermarketing\Queue\Entity\Connection;

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
     * The id of the entity
     *
     * @var string
     */
    protected $id;

    /**
     * Sets up a new entity manager with the connection details, the
     * entity type, and its id
     *
     * @param Connection $details
     * @param string $type
     * @param string $id
     */
    public function __construct(Connection $details, $type, $id)
    {
        parent::__construct($details);

        $this->type = $type;
        $this->id = $id;
    }

    protected function getHash()
    {
        return $this->type . ':' . $this->id;
    }
}
