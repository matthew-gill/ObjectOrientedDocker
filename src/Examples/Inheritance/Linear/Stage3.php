<?php

namespace MattGill\Examples\Inheritance\Linear;

class Stage3 extends Stage2
{
    protected function getLayers(): array
    {
        return [
            $this->run('echo "From Stage 3"'),
        ];
    }
}
