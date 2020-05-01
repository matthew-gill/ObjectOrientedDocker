<?php

namespace MattGill\Examples\Composition;

use MattGill\CompositionDockerfile;

class ComposedParts extends CompositionDockerfile
{
    public function configure(): void
    {
       $this->run('echo "Ive been composed!"');
    }

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
