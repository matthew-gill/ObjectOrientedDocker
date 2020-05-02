<?php

namespace MattGill\Examples\Composition\Simple;

use MattGill\Dockerfile;

class PartC extends Dockerfile
{
    public function getLayers(): array
    {
        return [
            $this->run('echo "From part C"'),
        ];
    }

    public function shouldIncludeFromInStage(): bool
    {
        return false;
    }
}
