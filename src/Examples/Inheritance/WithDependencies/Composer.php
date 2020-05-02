<?php

namespace MattGill\Examples\Inheritance\WithDependencies;

class Composer extends Base
{
    public function getLayers(): array
    {
        return [
            $this->add('./composer.json', '.'),
            $this->add('./composer.lock', '.'),
            $this->copy('--from=composer:1.7', '/usr/bin/composer', '/usr/local/bin/composer'),
            $this->run(
                'composer install',
                'composer clear-cache'
            )->setMultiline(true),
        ];
    }
}
