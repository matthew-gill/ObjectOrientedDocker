<?php

namespace MattGill;

use LogicException;

class MultiStage
{
    /**
     * @var Dockerfile[]
     */
    private $dockerfiles;

    /**
     * @var string[]
     */
    private $stages;

    /**
     * MultiStage constructor.
     *
     * @param Dockerfile ...$dockerfiles
     */
    public function __construct(Dockerfile ...$dockerfiles)
    {
        $this->dockerfiles = $dockerfiles;
        $this->populateStages();
    }

    private function populateStages(): void
    {
        $layers = $this->getAllLayers();

        foreach ($layers as $layer) {
            $stageName = $layer->getStageName();

            if (null === $stageName) {
                continue;
            }

            if (in_array($stageName, $this->stages)) {
                throw new LogicException("The stage {$stageName} already exists in this MultiStage");
            }

            $this->stages[] = $stageName;
        }
    }

    /**
     * @return array|Model\Layer[]
     */
    protected function getAllLayers(): array
    {
        $layers = [];

        foreach ($this->dockerfiles as $dockerfile) {
            /** @noinspection AdditionOperationOnArraysInspection */
            $layers += $dockerfile->getLayers();
        }

        return $layers;
    }
}
