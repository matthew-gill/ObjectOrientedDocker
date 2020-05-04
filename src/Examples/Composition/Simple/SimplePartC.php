<?php

namespace MattGill\Examples\Composition\Simple;

use MattGill\CompositionStage;

class SimplePartC extends CompositionStage
{
    protected function getLayers(): array
    {
        return [
            $this->run('echo "From part C"'),
        ];
    }
}
