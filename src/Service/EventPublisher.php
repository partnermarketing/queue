<?php

namespace Partnermarketing\Queue\Service;

use Partnermarketing\Queue\Entity\Connection;
use Partnermarketing\Queue\Entity\Stream;
use Partnermarketing\Queue\Entity\Queue;
use Partnermarketing\Queue\Listener\QueueListener;
use Redis;

/**
 * A service which is able to publish events to a stream
 */
class EventPublisher extends RedisService
{

    const REQUEST_LIVE_TIME = 30;
    /**
     * The stream this publisher sends events too
     *
     * @var Stream
     */
    protected $stream;

    /**
     * Constructs the event publisher
     *
     * @param Connection $details
     * @param Stream $stream
     */
    public function __construct(Connection $details, Stream $stream)
    {
        parent::__construct($details);

        $this->stream = $stream;
    }

    /**
     * Queries the redis queue set for this stream and returns a queue
     * for each of them
     *
     * @return array<Queue>
     */
    public function getStreamQueues()
    {
        $queues = $this->conn->sMembers($this->stream->getQueueSet());
        foreach ($queues as $queue) {
            yield new Queue($queue, $this->stream);
        }
    }

    /**
     * Publishes an event to a stream by adding it to each of its queues
     *
     * @param mixed $event
     */
    public function addEvent($event)
    {
        $eventData = json_encode($event);

        foreach ($this->getStreamQueues() as $queue) {
            $this->conn->lPush($queue->getList(), $eventData);
            $this->conn->setTimeout($queue->getList(), self::REQUEST_LIVE_TIME);
        }
    }

    public function scheduleCleanup()
    {
        $this->conn->setTimeout($this->stream->getQueueSet(), self::REQUEST_LIVE_TIME);
    }
}
