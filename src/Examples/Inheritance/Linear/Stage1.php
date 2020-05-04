<?php

namespace MattGill\Examples\Inheritance\Linear;

use MattGill\Dockerfile;

class Stage1 extends Dockerfile
{
    protected function getLayers(): array
    {
        return [
            $this->run('echo "From Stage 1"'),
        ];
    }

    protected function getBaseImage(): string
    {
        return 'ubuntu:latest';
    }
}
