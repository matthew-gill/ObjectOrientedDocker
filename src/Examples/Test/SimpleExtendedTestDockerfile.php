<?php

namespace MattGill\Examples\Test;

class SimpleExtendedTestDockerfile extends TestDockerfile
{
    public function configure(): void
    {
        $this->run("echo", '"just a simple extended test"');
    }
}
