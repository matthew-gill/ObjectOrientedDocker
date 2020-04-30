<?php

namespace Snapshot;

use MattGill\Dockerfile;
use PHPUnit\Framework\TestCase;

abstract class SnapshotTest extends TestCase
{
    public function testSnapshot(): void
    {
        $snapshotclass = $this->getClassToSnapshot();

        /** @var Dockerfile $dockerfile */
        $dockerfile = new $snapshotclass();

        $this->assertSame(
            $this->getExpectedSnapshot(),
            $dockerfile->compile()
        );
    }

    abstract protected function getClassToSnapshot(): string;

    abstract protected function getExpectedSnapshot(): string;
}
