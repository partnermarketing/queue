<?php

namespace Partnermarketing\Queue\Test\Entity;

use PHPUnit\Framework\TestCase;
use Partnermarketing\Queue\Entity\Stream;
use Partnermarketing\Queue\Entity\Queue;

class QueueTest extends TestCase
{
    private $queue;

    private $stream;

    public function setUp(): void
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

    public function testGetName()
    {
        $this->assertSame('test', $this->queue->getName());
    }

    public function testGetStream()
    {
        $this->assertSame($this->stream, $this->queue->getStream());
    }
}
