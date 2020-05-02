<?php

namespace MattGill\Examples\Composition;

use MattGill\ComponentDockerfile;

class PartD extends ComponentDockerfile
{
    public function configure(): void
    {
        $this->run('echo', 'from part D');
    }
}
