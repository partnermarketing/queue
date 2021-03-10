<?php

namespace Partnermarketing\Queue\Test\Listener;

use Partnermarketing\Queue\Listener\CallbackEntityListener;
use PHPUnit\Framework\TestCase;

/**
 * Tests that the CallbackEntityListener works as expected
 */
class CallbackEntityListenerTest extends TestCase
{
    /**
     * The listener we are testing
     */
    private $listener;

    /**
     * Mock callback to set callback expectations
     */
    private $mockCallback;

    /**
     * Sets up the test
     */
    public function setUp(): void
    {
        $this->mockCallback = $this->getMockBuilder(\stdClass::class)->addMethods(['callback'])->getMock();

        $this->listener = new CallbackEntityListener(
            [$this->mockCallback, 'callback']
        );
    }

    /**
     * Tests that execute() calls the callback we set in the constructor
     */
    public function testWithEntity()
    {
        $event = ['1', '2', '3'];

        $this->mockCallback->expects($this->once())
            ->method('callback')
            ->with($event);


        $this->listener->withEntity($event);
    }
}
