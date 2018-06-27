<?php

namespace Partnermarketing\Queue\Listener;

/**
 * A service which handles entity notices coming in
 */
interface EntityListener
{
    /**
     * Executes when an entity comes in
     *
     * @param string|array $entity
     */
    public function withEntity($entity);
}
