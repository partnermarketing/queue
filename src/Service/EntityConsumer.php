<?php

namespace Partnermarketing\Queue\Service;

use Partnermarketing\Queue\Entity\Stream;

/**
 * A service that is able to read and request entity data from redis
 */
class EntityConsumer extends EntityManager
{
    /**
     * Publishes an event to request entity data from another service
     */
    private function requestEntity()
    {
        (new EventPublisher($this->details))->addEvent(
            new Stream($this->type),
            ['uuid' => $this->id]
        );
    }

    /**
     * Blocks until it recieves an advertisment that the entity it wants
     * has been created
     */
    private function waitForEntity()
    {
        $id = $this->id;
        $this->createConnection()->subscribe(
            [$this->type],
            function($conn, $channel, $msg) use ($id) {
                if ($msg == $id) {
                    $conn->close();
                }
            }
        );
    }

    /**
     * Gets data from the redis hash
     *
     * @param array $items
     * @return array The requested data
     */
    private function getData($items)
    {
        $data = $this->conn->hMGet($this->getHash(), $items);

        return !count($data) ? null : $data;
    }

    /**
     * Tries to load a single data value for the entity, optionally
     * requsting it if it does not already exist
     *
     * @param string $item
     * @param boolean $request
     * @return mixed The value
     * @see getEntityValues()
     */
    public function getEntityValue($item, $request = true)
    {
        $data = $this->getEntityValues([$item], $request);

        return $data[$item];
    }

    /**
     * Tries to load the data for the given entity, optionally
     * requesting if it does not already exist
     *
     * @param array $items The items of the entity to get
     * @param boolean $request
     * @return array The requested data
     */
    public function getEntityValues($items, $request = true)
    {
        $data = $this->getData($items);

        if (!$request || $data) {
            return $data;
        }

        $this->requestEntity();
        $this->waitForEntity();

        return $this->getData($items);
    }
}
