<?php

namespace Partnermarketing\Queue\Service;

use Partnermarketing\Queue\Entity\Connection;
use Partnermarketing\Queue\Traits\HasConnection;

/**
 * An abstract service that is able to work with entities
 */
abstract class EntityManager
{
    use HasConnection {
        // Rewrite the trait-provided constructor as we still need to be
        // able to call it from our own constructor
        __construct as private init;
    }

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
        $this->init($details);
        $this->type = $type;
        $this->id = $id;
    }

    protected function getHash()
    {
        return $this->type . ':' . $this->id;
    }
}
