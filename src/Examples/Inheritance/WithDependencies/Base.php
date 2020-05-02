<?php

namespace MattGill\Examples\Inheritance\WithDependencies;

use MattGill\Dockerfile;

class Base extends Dockerfile
{
    public function getLayers(): array
    {
        return [
            $this->run(
                'apt-get update --fix-missing',
                'apt-get install default-mysql-client \
                                 openssh-server \
                                 unzip \
                                 zip'
            )->setMultiline(true)->setComment("Install required packages"),
        ];
    }

    public function getBaseImage(): string
    {
        return 'php:7-apache';
    }

}
