<?php

namespace MattGill\Examples\Composition\Simple;

use MattGill\ComposedDockerfile;

class SimpleComposition extends ComposedDockerfile
{
    public function getComposition(): array
    {
        return [
            PartA::class,
            PartB::class,
            PartC::class,
        ];
    }

    public function getBaseImage(): string
    {
        return 'busybox:latest';
    }
}
