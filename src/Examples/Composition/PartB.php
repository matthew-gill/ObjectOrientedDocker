<?php

namespace MattGill\Examples\Composition;

use MattGill\ComponentDockerfile;

class PartB extends ComponentDockerfile
{
    public function configure(): void
    {
        $this->run('echo', 'from part B');
    }
}
