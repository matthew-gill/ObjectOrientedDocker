<?php

namespace MattGill\Examples\Test;

use MattGill\Dockerfile;

class TestDockerfile extends Dockerfile
{
    public function configure(): void
    {
        $this->run('echo', '"hello"');
        $this->add('somefile.txt', '.');
        $this->copy('someotherfile.txt', '.');
        $this->cmd('ls -lah');
    }

    public function getRootImage(): string
    {
       return 'the-root-image:some-tag';
    }
}
