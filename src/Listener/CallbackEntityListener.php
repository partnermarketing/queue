<?php

namespace Partnermarketing\Queue\Listener;

/**
 * Handles Listeners by calling whatever callable was given to it in its
 * constructor
 */
class CallbackEntityListener implements EntityListener
{
    /**
     * The callback to use when withEntity is called
     *
     * @var callable
     */
    private $callback;

    /**
     * Constructs the handler with callback to
     * run when the withEntity method is called
     */
    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    /**
     * {@inheritDoc}
     */
    public function withEntity($event)
    {
        $cb = $this->callback;

        return $cb($event);
    }
}
