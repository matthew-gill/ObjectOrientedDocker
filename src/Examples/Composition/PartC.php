<?php

namespace MattGill\Examples\Composition;

use MattGill\ComponentDockerfile;

class PartC extends ComponentDockerfile
{
    public function configure(): void
    {
        $this->run('echo', 'from part C');
    }
}
