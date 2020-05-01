<?php

namespace MattGill\Snapshot;

use MattGill\Examples\Composition\ComposedParts;

class CompositionTest extends SnapshotTest
{
    protected function getClassToSnapshot(): string
    {
        return ComposedParts::class;
    }

    protected function getExpectedSnapshot(): string
    {
        return <<<EXPECTED
FROM ubuntu
RUN echo from part A

RUN echo from part b

RUN echo "Ive been composed!"
EXPECTED;

    }
}
