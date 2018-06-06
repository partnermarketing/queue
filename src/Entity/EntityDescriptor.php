<?php

namespace Partnermarketing\Queue\Entity;

/**
 * Describes an entity
 *
 */
class EntityDescriptor
{
    /**
     * The name of the entity
     *
     * @var string
     */
    private $name;

    /**
     * Sets up the entity based on its name
     *
     * @var string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Gets the entity's name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the entity as a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}

