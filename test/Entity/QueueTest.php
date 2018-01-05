<?php

namespace Partnermarketing\Queue\Test\Entity;

use PHPUnit\Framework\TestCase;
use Partnermarketing\Queue\Entity\Stream;
use Partnermarketing\Queue\Entity\Queue;

class QueueTest extends TestCase
{
    private $queue;

    private $stream;

    public function setUp()
    {
        $this->stream = new Stream('test_stream');
        $this->queue = new Queue('test', $this->stream);
    }

    public function testGetList()
    {
        $this->assertSame(
            'test_stream:queues:test',
            $this->queue->getList()
        );
    }

    public function testGetServiceId()
    {
        $this->assertSame('test', $this->queue->getServiceId());
    }

    public function testGetStream()
    {
        $this->assertSame($this->stream, $this->queue->getStream());
    }
}
