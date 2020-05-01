<?php

namespace MattGill;

abstract class CompositionDockerfile extends Dockerfile
{
    public function configure(): void
    {
        foreach ($this->getDependentStages() as $dockerfileClass) {
            /** @var Dockerfile $dockerfile */
            $dockerfile = new $dockerfileClass(false);
            $dockerfile->configure();
        }
    }
}
