<?php

namespace Partnermarketing\Queue\Service;

use Partnermarketing\Queue\Entity\Stream;
use Partnermarketing\Queue\Entity\Queue;
use Partnermarketing\Queue\Listener\QueueListener;
use Redis;

/**
 * A service which is able to publish events to a stream
 */
class EventPublisher extends RedisService
{
    /**
     * Queries the redis queue set for this stream and returns a queue
     * for each of them
     *
     * @param Stream $stream
     * @return array<Queue>
     */
    public function getStreamQueues(Stream $stream)
    {
        $queues = $this->conn->sMembers($stream->getQueueSet());
        foreach ($queues as $queue) {
            yield new Queue($queue, $stream);
        }
    }

    /**
     * Publishes an event to a stream by adding it to each of its queues
     *
     * @param Stream $stream
     * @param mixed $event
     */
    public function addEvent(Stream $stream, $event)
    {
        $eventData = json_encode($event);

        foreach ($this->getStreamQueues($stream) as $queue) {
            $this->conn->lPush($queue->getList(), $eventData);
        }
    }
}
