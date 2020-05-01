<?php

namespace MattGill\Examples\Composition;

use MattGill\ComponentDockerfile;

class PartA extends ComponentDockerfile
{
    public function configure(): void
    {
        $this->run('echo', 'from part A');
    }
}
