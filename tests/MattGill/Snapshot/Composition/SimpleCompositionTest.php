<?php

namespace MattGill\Snapshot\Composition;

use MattGill\Examples\Composition\Simple\SimpleComposition;
use MattGill\Snapshot\SnapshotTest;

class SimpleCompositionTest extends SnapshotTest
{

    protected function getClassToSnapshot(): string
    {
        return SimpleComposition::class;
    }

    protected function getExpectedSnapshot(): string
    {
        return <<<STR
FROM busybox:latest
RUN echo "From part A"
RUN echo "From part B"
RUN echo "From part C"
STR;
    }
}
