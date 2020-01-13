<?php

namespace Partnermarketing\Queue\Service;

use Partnermarketing\Queue\Entity\Connection;
use RuntimeException;

/**
 * Manages a lock in the redis instance
 */
class Padlock extends RedisService
{
    /**
     * The name of the lock this manages
     *
     * @var string
     */
    private $name;

    /**
     * The id of this padlock; used to check lock ownership
     *
     * @var string
     */
    private $id;

    /**
     * Constructs the Padlock service
     *
     * @param string $name The name of the lock to manage
     * @param Connection $details Connection settings
     */
    public function __construct($name, Connection $details)
    {
        parent::__construct($details);

        $this->name = $name;
        $this->id = uniqid('', true);
    }

    /**
     * Checks if this padlock is locked
     *
     * @return bool
     */
    public function isLocked()
    {
        return $this->conn->exists('lock:' . $this->name);
    }

    /**
     * Checks if this padlock is unlocked
     *
     * @return bool
     */
    public function isUnlocked()
    {
        return !$this->isLocked();
    }

    /**
     * Gets the owner of this lock
     *
     * @return string
     */
    public function owner()
    {
        return $this->conn->get('lock:' . $this->name);
    }

    /**
     * Checks if this padlock owns the lock
     *
     * @return bool
     */
    public function ownsLock()
    {
        return $this->owner() === $this->id;
    }

    /**
     * Unlocks the lock, either throwing an exception if it can't, or
     * optionally allowing it if you choose to force
     *
     * @param bool $force
     * @throws RuntimeException
     */
    public function unlock($force = false)
    {
        if (!$force && $this->isLocked() && !$this->ownsLock()) {
            throw new RuntimeException(
                'Cannot unlock'
            );
        }

        $this->conn->del(['lock:' . $this->name]);
    }

    /**
     * Attempts to lock, throwing an exception if it is already
     *
     * @throws RuntimeException
     */
    public function lock()
    {
        if ($this->isLocked()) {
            throw new RuntimeException('Cannot lock: already locked');
        }

        $this->conn->set('lock:' . $this->name, $this->id);
    }
}
