<?php

namespace MattGill\Examples;

use MattGill\Dockerfile;

class ExampleDockerfile extends Dockerfile
{
    public function configure(): void
    {
        $this->from('ubuntu');

        $this->run('apt-get update', 'apt-get install')
            ->setMultiline()
            ->setComment("Update to latest");

        $this->run('apt-get install -y', 'nginx');

        $this->entrypoint("/usr/sbin/nginx", "-g", "daemon off;");
        $this->expose(80);
    }

    public function getRootImage(): string
    {
        return 'ubuntu-example';
    }
}
