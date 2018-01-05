<?php

namespace Partnermarketing\Queue\Service;

/**
 * A service that is able to save entities into a redis hash
 */
class EntityProvider extends EntityManager
{
    /**
     * Saves the given entity and data, and advertises that it has done
     * so, optionally advertising a second time if noone picked it up
     *
     * @param array $data
     * @param boolean $retry
     */
    public function save($data, $retry = true)
    {
        $data['uuid'] = $this->id;

        $this->conn->hMSet($this->getHash(), $data);

        // Publish to a channel that the entity has been created
        // In most cases we have been asked for this, so we'd expect
        // someone to be listening. In the unlikely case that noone was,
        // be generous and try again in 10 seconds in case there was a
        // race condition
        if (!$this->advertise() && $retry) {
            sleep(10);
            $this->advertise();
        }
    }

    /**
     * Advertises that an entity as been cached
     */
    private function advertise()
    {
        return $this->conn->publish($this->type, $this->id);
    }
}
