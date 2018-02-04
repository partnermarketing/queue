<?php

namespace Partnermarketing\Queue\Exception;

use RuntimeException;

/**
 * Thrown when the listenerHandler is asked to listen, but there aren't
 * any registered listener
 */
class NoListenersException extends RuntimeException
{
    //
}

