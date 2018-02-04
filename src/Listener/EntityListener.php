<?php

namespace Partnermarketing\Queue\Listener;

use Partnermarketing\Queue\Entity\Queue;

/**
 * A service which handles entity notices coming in
 */
interface EntityListener
{
    /**
     * Executes when an entity comes in
     *
     * @param string $entity
     */
    public function withEntity($entity);
}
