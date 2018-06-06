<?php

namespace Partnermarketing\Queue\Entity;

use RuntimeException;

/**
 * Represents a reference to some content
 */
class ContentReference
{
    /**
     * The entity type in use
     *
     * @var EntityDescriptor
     */
    private $entity;

    /**
     * The uuid of the entity
     *
     * @var string
     */
    private $uuid;

    /**
     * Constructs the content reference
     *
     * @param EntityDescriptor $entity
     * @param string $uuid
     */
    public function __construct(EntityDescriptor $entity, $uuid)
    {
        $this->entity = $entity;
        $this->uuid = $uuid;
    }

    /**
     * Builds the model from a string content reference
     *
     * @param string $_ref
     * @return ContentReference
     */
    public static function fromRef($_ref)
    {
        $ref = explode(':', $_ref);

        if (count($ref) !== 2) {
            throw new RuntimeException(
                'Invalid content reference'
            );
        }

        return new static(new EntityDescriptor($ref[0]), $ref[1]);
    }

    /**
     * Returns the entity descriptor
     *
     * @return EntityDescriptor
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Returns the uuid
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Returns the string representation of the content reference
     *
     * @return string
     */
    public function __toString()
    {
        return $this->entity . ':' . $this->uuid;
    }
}

