<?php

namespace MattGill\Examples\Composition\Simple;

use MattGill\CompositionDockerfile;

class SimpleComposition extends CompositionDockerfile
{
    public function getComposition(): array
    {
        return [
            SimplePartA::class,
            SimplePartB::class,
            SimplePartC::class,
        ];
    }

    protected function getBaseImage(): string
    {
        return 'busybox:latest';
    }
}
