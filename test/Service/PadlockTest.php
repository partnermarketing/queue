<?php

namespace Partnermarketing\Queue\Test\Service;

use Partnermarketing\Queue\Entity\Connection;
use Partnermarketing\Queue\Service\Padlock;
use Partnermarketing\Queue\Service\RedisService;
use PHPUnit\Framework\TestCase;
use Redis;
use ReflectionClass;
use RuntimeException;

/**
 * Tests that the Padlock service acts as it should
 */
class PadlockTest extends TestCase
{
    /**
     * The object we are testing
     *
     * @var Padlock
     */
    private $object;

    /**
     * A mock for the redis connection
     *
     * @var Redis
     */
    private $conn;

    /**
     * A reflector for the service we are testing
     *
     * @var ReflectionClass
     */
    private $reflect;

    public function setUp(): void
    {
        RedisService::setTestMode();

        $this->object = new Padlock('testlock', new Connection());

        $this->conn = $this->getMockBuilder(Redis::class)
            ->disableOriginalConstructor()
            ->setMethods(['exists', 'get', 'del', 'set'])
            ->getMock();

        $this->reflect = new ReflectionClass(Padlock::class);
        $conn = $this->reflect->getProperty('conn');
        $conn->setAccessible(true);
        $conn->setValue($this->object, $this->conn);
    }

    /**
     * Sets the id of the padlock to 123
     */
    private function setId()
    {
        $prop = $this->reflect->getProperty('id');
        $prop->setAccessible(true);
        $prop->setValue($this->object, '123');
    }

    /**
     * Tests that isLocked() returns true when the lock exists
     */
    public function testIsLockedWhenLocked()
    {
        $this->conn->expects($this->once())
            ->method('exists')
            ->with('lock:testlock')
            ->willReturn(true);

        $this->assertTrue($this->object->isLocked());
    }

    /**
     * Tests that isLocked() returns false when the lock doesn't exist
     */
    public function testIsLockedWhenUnlocked()
    {
        $this->conn->expects($this->once())
            ->method('exists')
            ->with('lock:testlock')
            ->willReturn(false);

        $this->assertFalse($this->object->isLocked());
    }

    /**
     * Tests that isUnlocked() reutrns false when the lock exists
     */
    public function testIsUnlockedWhenLocked()
    {
        $this->conn->expects($this->once())
            ->method('exists')
            ->with('lock:testlock')
            ->willReturn(true);

        $this->assertFalse($this->object->isUnlocked());
    }

    /**
     * Tests that isUnlocked() returns true when the lock does not
     * exist
     */
    public function testIsUnlockedWhenUnlocked()
    {
        $this->conn->expects($this->once())
            ->method('exists')
            ->with('lock:testlock')
            ->willReturn(false);

        $this->assertTrue($this->object->isUnlocked());
    }

    /**
     * Tests that owner gives the owner of the lock
     */
    public function testOwner()
    {
        $this->conn->expects($this->once())
            ->method('get')
            ->with('lock:testlock')
            ->willReturn('123456');

        $this->assertSame('123456', $this->object->owner());
    }

    /**
     * Tests that ownsLock() returns false when the id does not match
     * the current id
     */
    public function testOwnsLockMismatch()
    {
        $this->setId();
        $this->conn->expects($this->once())
            ->method('get')
            ->with('lock:testlock')
            ->willReturn('456');

        $this->assertFalse($this->object->ownsLock());
    }

    /**
     * Tests that ownsLock() returns true when the id does match the
     * current id
     */
    public function testOwnsLockMatch()
    {
        $this->setId();
        $this->conn->expects($this->once())
            ->method('get')
            ->with('lock:testlock')
            ->willReturn('123');

        $this->assertTrue($this->object->ownsLock());
    }

    /**
     * Tests that unlock, with force disabled checks and fails if it
     * can't do the unlock
     */
    public function testUnlockFailure()
    {
        $this->setId();

        $this->conn->expects($this->once())
            ->method('exists')
            ->with('lock:testlock')
            ->willReturn(true);

        $this->conn->expects($this->once())
            ->method('get')
            ->with('lock:testlock')
            ->willReturn('456');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot unlock');

        $this->object->unlock(false);
    }

    /**
     * Tests that unlock, with force disabled checks but succeeds if
     * it is not already locked
     */
    public function testUnlockNoForceNotLocked()
    {
        $this->setId();
        $this->conn->expects($this->once())
            ->method('exists')
            ->with('lock:testlock')
            ->willReturn(false);

        $this->conn->expects($this->once())
            ->method('del')
            ->with('lock:testlock');

        $this->object->unlock(false);
    }

    /**
     * Test that unlock, with force disabled checks but succeeds if it
     * is locked but it is the owner
     */
    public function testUnlockNoForceOwner()
    {
        $this->setId();

        $this->conn->expects($this->once())
            ->method('exists')
            ->with('lock:testlock')
            ->willReturn(true);

        $this->conn->expects($this->once())
            ->method('get')
            ->with('lock:testlock')
            ->willReturn('123');

        $this->conn->expects($this->once())
            ->method('del')
            ->with('lock:testlock');

        $this->object->unlock(false);
    }

    /**
     * Tests that, with force enabled, no checks are done and it
     * succeeds
     */
    public function testUnlockWithForce()
    {
        $this->conn->expects($this->never())->method('exists');

        $this->conn->expects($this->once())
            ->method('del')
            ->with('lock:testlock');

        $this->object->unlock(true);
    }

    /**
     * Tests that, if already locked, lock() fails
     */
    public function testLockFailure()
    {
        $this->conn->expects($this->once())
            ->method('exists')
            ->with('lock:testlock')
            ->willReturn(true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot lock: already locked');

        $this->object->lock();
    }

    /**
     * Tests that, if not already locked, lock sets the lock up with
     * its id
     */
    public function testLockSuccess()
    {
        $this->setId();
        $this->conn->expects($this->once())
            ->method('exists')
            ->with('lock:testlock')
            ->willReturn(false);

        $this->conn->expects($this->once())
            ->method('set')
            ->with('lock:testlock', '123');

        $this->object->lock();
    }
}
