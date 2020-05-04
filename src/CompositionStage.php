<?php

namespace MattGill;

/**
 * Just a set of layers which can be used to compose a dockerfile.
 */
abstract class CompositionStage extends Dockerfile
{
    /**
     * Given a composition stage is just a set of layers, we don't need to automatically add a FROM layer.
     *
     * @return bool
     */
    protected function shouldAutomaticallyAddFromStage(): bool
    {
        return false;
    }
}
