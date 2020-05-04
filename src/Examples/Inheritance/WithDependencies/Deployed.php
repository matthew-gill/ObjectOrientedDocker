<?php

namespace MattGill\Examples\Inheritance\WithDependencies;

class Deployed extends Base
{
    protected function getLayers(): array
    {
        return [
            $this->copyFromStage(Composer::class, 'node_modules', 'node_modules'),
            $this->user('root'),
            $this->run('httpd -DFOREGROUND'),
        ];
    }
    protected function getDependentStages(): array
    {
        return [
            Composer::class,
        ];
    }
}
