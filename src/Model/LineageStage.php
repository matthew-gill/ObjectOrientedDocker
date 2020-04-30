<?php

namespace MattGill\Model;

use MattGill\Dockerfile;

class LineageStage
{
    /**
     * @var Dockerfile
     */
    private $stage;

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

    public function getFrom(): string
    {
        // if this class implements `getRootImage` directly, then return it otherwise do the sluggify

        $refClass = new \ReflectionClass($this->stage);
        $method = $refClass->getMethod('getRootImage');

        if ($method->class === $refClass->getName()) {
            return $this->stage->getRootImage();
        }

        return $this->sluggifyClassName(get_parent_class($this->stage));
    }

    public function getStageName(): string
    {
        return $this->sluggifyClassName(get_class($this->stage));
    }

    private function sluggifyClassName(string $className): string
    {
        return str_replace('\\', '-', strtolower($className));
    }

}
