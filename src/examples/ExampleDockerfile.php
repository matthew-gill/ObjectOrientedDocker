<?php

namespace MattGill\Examples;

use MattGill\Dockerfile;

class ExampleDockerfile extends Dockerfile
{
    protected function getImageName(): string
    {
        return 'ubuntu-example';
    }

    protected function getTag(): string
    {
        return 'latest';
    }

    protected function configure(): void
    {
        $this->from('ubuntu')
            ->setStageName('theexample');

        $this->run('apt-get update', 'apt-get install')
            ->setMultiline()
            ->setComment("Update to latest");

        $this->run('apt-get install -y', 'nginx');

        $this->entrypoint("/usr/sbin/nginx", "-g", "daemon off;");
        $this->expose(80);
    }
}
