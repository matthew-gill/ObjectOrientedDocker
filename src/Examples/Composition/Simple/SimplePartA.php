<?php

namespace MattGill\Examples\Composition\Simple;

use MattGill\CompositionStage;

class SimplePartA extends CompositionStage
{
    protected function getLayers(): array
    {
        return [
            $this->run('echo "From part A"'),
        ];
    }
}
