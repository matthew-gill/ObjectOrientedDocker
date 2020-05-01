<?php

namespace MattGill\Examples\Composition;

use MattGill\CompositionDockerfile;

class ComposedParts extends CompositionDockerfile
{
    public function getDependentStages(): array
    {
        return [
            PartA::class,
            PartB::class,
        ];
    }

    public function getRootImage(): string
    {
        return 'ubuntu';
    }
}
