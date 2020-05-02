<?php

namespace MattGill;

abstract class CompositionDockerfile extends Dockerfile
{
    public function getDependentStagesAfter(): array
    {
        return [];
    }
}
