<?php

namespace MattGill\Snapshot\Inheritance;

use MattGill\Examples\Inheritance\Linear\Stage3;
use MattGill\Snapshot\SnapshotTest;

class LinearInheritanceSnapshotTest extends SnapshotTest
{
    protected function getClassToSnapshot(): string
    {
        return Stage3::class;
    }

    protected function getExpectedSnapshot(): string
    {
        return <<<STR
FROM ubuntu:latest AS mattgill-examples-inheritance-linear-stage1
RUN echo "From Stage 1"

FROM mattgill-examples-inheritance-linear-stage1 AS mattgill-examples-inheritance-linear-stage2
RUN echo "From Stage 2"

FROM mattgill-examples-inheritance-linear-stage2 AS mattgill-examples-inheritance-linear-stage3
RUN echo "From Stage 3"
STR;

    }
}
