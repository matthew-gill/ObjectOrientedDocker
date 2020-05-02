<?php

namespace MattGill\Examples\Inheritance\Linear;

class Stage2 extends Stage1
{
    public function getLayers(): array
    {
        return [
            $this->run('echo "From Stage 2"'),
        ];
    }
}
