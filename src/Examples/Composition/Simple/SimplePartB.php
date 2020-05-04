<?php

namespace MattGill\Examples\Composition\Simple;

use MattGill\CompositionStage;

class SimplePartB extends CompositionStage
{
    protected function getLayers(): array
    {
        return [
            $this->run('echo "From part B"'),
        ];
    }
}
