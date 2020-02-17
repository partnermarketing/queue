<?php

namespace Partnermarketing\Stream\Test\Entity;

use PHPUnit\Framework\TestCase;
use Partnermarketing\Queue\Entity\Stream;

class StreamTest extends TestCase
{
    private $stream;

    protected function setUp(): void
    {
        $this->stream = new Stream('test_stream');
    }

    public function testGetName()
    {
        $this->assertSame('test_stream', $this->stream->getName());
    }

    public function testGetListenerSet()
    {
        $this->assertSame(
            'test_stream:queues',
            $this->stream->getQueueSet()
        );
    }
}
