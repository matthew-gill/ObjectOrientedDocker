<?php

namespace MattGill\Examples\Composition\Simple;

use MattGill\Dockerfile;

class PartB extends Dockerfile
{
    public function getLayers(): array
    {
        return [
            $this->run('echo "From part B"'),
        ];
    }

    public function shouldIncludeFromInStage(): bool
    {
        return false;
    }
}
