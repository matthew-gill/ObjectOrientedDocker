<?php

namespace MattGill\Model;

use MattGill\Dockerfile;
use MattGill\Utils;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

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
     */
    public function getFrom(): string
    {
        // if this class implements `getRootImage` directly, then return it otherwise do the sluggify

        try {
            $refClass = new ReflectionClass($this->stage);
            $method = $refClass->getMethod('getRootImage');
        } catch (ReflectionException $e) {
            throw new RuntimeException("Could not obtain dockerfile class reflection. Check inheritance issues.");
        }

        if ($method->class === $refClass->getName()) {
            return $this->stage->getRootImage();
        }

        return Utils::convertClassNameToStageName(get_parent_class($this->stage));
    }

    /**
     * @return string
     */
    public function getStageName(): string
    {
        return Utils::convertClassNameToStageName(get_class($this->stage));
    }

    /**
     * @return Dockerfile
     */
    public function getStage(): Dockerfile
    {
        return $this->stage;
    }
}
