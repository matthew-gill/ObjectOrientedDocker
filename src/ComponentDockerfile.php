<?php

namespace MattGill;

use LogicException;

abstract class ComponentDockerfile extends Dockerfile
{
    public function getRootImage(): string
    {
        throw new LogicException("A component must not have a root image");
    }
}
