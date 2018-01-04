<?php

namespace Partnermarketing\Queue\Test\Entity;

use PHPUnit\Framework\TestCase;
use Partnermarketing\Queue\Entity\Queue;

class QueueTest extends TestCase
{
    private $queue;

    public function setUp()
    {
        $this->queue = new Queue('test_queue');
    }

    public function testGetName()
    {
        $this->assertSame('test_queue', $this->queue->getName());
    }

    public function testGetListenerSet()
    {
        $this->assertSame(
            'test_queue:listeners',
            $this->queue->getListenerSet()
        );
    }
}
