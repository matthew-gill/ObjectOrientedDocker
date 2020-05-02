<?php

namespace MattGill\Examples\Composition\Simple;

use MattGill\Dockerfile;

class PartA extends Dockerfile
{
    public function getLayers(): array
    {
        return [
            $this->run('echo "From part A"'),
        ];
    }

    public function shouldIncludeFromInStage(): bool
    {
        return false;
    }
}
