<?php

namespace MattGill\Model;

use PHPUnit\Framework\TestCase;

class NoopTest extends TestCase
{
    public function testCompileReturnsEmpty(): void
    {
        $noop = new Noop();
        $this->assertSame("", $noop->compile());
    }

    public function testCommentsThrowException(): void
    {
        $noop = new Noop();
        $this->expectException(\LogicException::class);

        $noop->setComment("Test");
    }

    public function testMultilineThrowException(): void
    {
        $noop = new Noop();
        $this->expectException(\LogicException::class);

        $noop->setMultiline(true);
    }
}
