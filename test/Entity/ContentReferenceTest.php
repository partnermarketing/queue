<?php

namespace Partnermarketing\Queue\Test\Entity;

use Partnermarketing\Queue\Entity\ContentReference;
use Partnermarketing\Queue\Entity\EntityDescriptor;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ContentReferenceTest extends TestCase
{
    /**
     * Tests that fromRef acts as expected
     */
    public function testFromRef()
    {
        $reference = ContentReference::fromRef('test:123');

        $this->assertInstanceOf(ContentReference::class, $reference);
        $this->assertSame('test', $reference->getEntity()->getName());
        $this->assertSame('123', $reference->getUuid());
    }

    /**
     * Tests that fromRef throws an exception when the reference is
     * bad
     */
    public function testFromRefError()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid content reference');

        ContentReference::fromRef('123');
    }

    /**
     * Tests that __toString works as we expect
     */
    public function testToString()
    {
        $reference = new ContentReference(
            new EntityDescriptor('test'),
            '123'
        );

        $this->assertSame('test:123', $reference->__toString());
    }
}
