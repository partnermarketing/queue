<?php

namespace Partnermarketing\Queue\Test\Entity;

use PHPUnit\Framework\TestCase;
use Partnermarketing\Queue\Entity\Listener;
use Partnermarketing\Queue\Entity\Queue;

class ListenerTest extends TestCase
{
    private $listener;

    private $queue;

    public function setUp()
    {
        $this->queue = new Queue('test_queue');
        $this->listener = new Listener('test', $this->queue);
    }

    public function testGetList()
    {
        $this->assertSame(
            'test_queue:listeners:test',
            $this->listener->getList()
        );
    }

    public function testGetServiceId()
    {
        $this->assertSame('test', $this->listener->getServiceId());
    }

    public function testGetQueue()
    {
        $this->assertSame($this->queue, $this->listener->getQueue());
    }
}
