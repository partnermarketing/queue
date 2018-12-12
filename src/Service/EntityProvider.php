<?php

namespace Partnermarketing\Queue\Service;

use InvalidArgumentException;

/**
 * A service that is able to save entities into a redis hash
 */
class EntityProvider extends EntityManager
{
    /**
     * The type of Stream that this sends event to
     *
     * @var string
     */
    const TYPE = 'response';

    /**
     * Saves the given entity and data, and advertises that it has done
     * so
     *
     * @param array $data
     * @param boolean $advertise
     */
    public function save($data, $advertise = true, $expire = 600)
    {
        if (!isset($data['uuid'])) {
            throw new InvalidArgumentException(
                'Provided data has no uuid'
            );
        }

        $id = $data['uuid'];

        $this->conn->hMSet($this->getHash($id), $data);
        $this->conn->setTimeout($this->getHash($id), $expire);

        if ($advertise) {
            $this->advertise($id);
        }
    }

    public function scheduleCleanup()
    {
        $this->eventPublisher->scheduleCleanup();
    }
}
