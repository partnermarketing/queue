<?php

namespace Partnermarketing\Queue\Test\Mock;

use Partnermarketing\Queue\Service\EntityManager;

/**
 * A stub which exists only to extend the EntityManager to make it
 * accessible
 */
class EntityManagerStub extends EntityManager
{
    const TYPE = 'test';

    /**
     * Just gets the eventPublisher
     */
    public function getEventPublisher()
    {
        return $this->eventPublisher;
    }

    /**
     * Just gets the details
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Just gets the type
     */
    public function getType()
    {
        return $this->type;
    }
}
