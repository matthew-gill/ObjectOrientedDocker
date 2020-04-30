<?php

namespace MattGill\Model;

use MattGill\Dockerfile;
use MattGill\Utils;
use ReflectionClass;
use ReflectionException;

class LineageStage
{
    /**
     * @var Dockerfile
     */
    private $stage;

    /**
     * LineageStage constructor.
     *
     * @param Dockerfile $stage
     */
    public function __construct(Dockerfile $stage)
    {
        $this->stage = $stage;
    }

    /**
     * @return Layer[]
     */
    public function getLayers(): array
    {
        $this->stage->configure();
        return $this->stage->getLayers();
    }

    /**
     * @return string
     * @throws ReflectionException
     */
    public function getFrom(): string
    {
        // if this class implements `getRootImage` directly, then return it otherwise do the sluggify

        $refClass = new ReflectionClass($this->stage);
        $method = $refClass->getMethod('getRootImage');

        if ($method->class === $refClass->getName()) {
            return $this->stage->getRootImage();
        }

        return Utils::sluggifyClassName(get_parent_class($this->stage));
    }

    /**
     * @return string
     */
    public function getStageName(): string
    {
        return Utils::sluggifyClassName(get_class($this->stage));
    }
}
