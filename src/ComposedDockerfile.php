<?php

namespace MattGill;

abstract class ComposedDockerfile extends Dockerfile
{
    /**
     * @return string[]
     */
    abstract public function getComposition(): array;

    /**
     * @param bool $withComments
     *
     * @return string
     */
    public function compile(bool $withComments = false): string
    {
        $compiled = '';

        $compiled .= $this->getFromLayer(false)->compile($withComments) . "\n";

        foreach ($this->getComposition() as $dockerfileClass) {
            /** @var Dockerfile $dockerfile */
            $dockerfile = new $dockerfileClass();
            $compiled .= $dockerfile->compile($withComments) . "\n";
        }

        return trim($compiled);
    }

    /**
     * @return array
     */
    public function getLayers(): array
    {
        throw new \LogicException("You should not define layers in Composed Dockerfiles, use getComposition instead.");
    }
}
