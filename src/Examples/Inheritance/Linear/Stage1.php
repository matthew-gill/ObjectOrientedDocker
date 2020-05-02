<?php

namespace MattGill\Examples\Inheritance\Linear;

use MattGill\Dockerfile;

class Stage1 extends Dockerfile
{
    public function getLayers(): array
    {
        return [
            $this->run('echo "From Stage 1"'),
        ];
    }

    public function getBaseImage(): string
    {
        return 'ubuntu:latest';
    }
}
