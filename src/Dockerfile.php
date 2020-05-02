<?php

namespace MattGill;

use LogicException;
use MattGill\Model\Layer;
use MattGill\Model\Noop;
use Pustato\TopSort\Collection;
use Pustato\TopSort\Contracts\Sortable;
use ReflectionClass;

abstract class Dockerfile implements Sortable
{
    public function getId(): string
    {
        return get_class($this);
    }

    public function compile(bool $withComments = false): string
    {
        $compiled = '';

        $layers = $this->compileLayers();
        foreach ($layers as $layer) {
            $compiled .= $layer->compile($withComments) . "\n";
        }

        return trim($compiled);
    }

    /**
     * @return Layer[]
     */
    public function compileLayers(): array
    {
        $classes = $this->getInvolvedClasses(get_class($this));
        $instanciated = [];

        foreach ($classes as $class) {
            $instanciated[] = new $class();
        }

        $assetsCollection = new Collection($instanciated);
        $result = $assetsCollection->getSorted();

        $layers = [];

        /** @var Dockerfile $stage */
        foreach ($result as $stage) {

            if ($this->shouldIncludeFromInStage()) {
                $layers[] = $stage->getFromLayer();
            }

            $layers = array_merge($layers, $stage->getLayers());
            $layers[] = new Noop();
        }

        return $layers;
    }


    public function getDependenciesForClass(string $className): array
    {
        $reflected = new ReflectionClass($className);
        $getDependentStagesMethod = $reflected->getMethod('getDependentStages');

        if ($getDependentStagesMethod->class !== $className) {
            return [];
        }

        /** @var Dockerfile $instanciated */
        $instanciated = new $className();

        $dependencies = [];

        foreach ($instanciated->getDependentStages() as $dependency) {
            /** @var Dockerfile $instanciatedDependency */
            $dependencies = array_merge($dependencies, $this->getClassHierarchy($dependency));
            $dependencies = array_merge($dependencies, $this->getDependenciesForClass($dependency));
        }

        return $dependencies;
    }

    public function getClassHierarchy(string $context): array
    {
        $hierarchy = [];

        while ($context) {
            $reflected = new ReflectionClass($context);

            if ($reflected->isAbstract()) {
                break;
            }

            $hierarchy[] = $context;

            $context = get_parent_class($context);
        }

        return $hierarchy;
    }


    public function getDependenciesIds(): array
    {
        $dependencies = [];

        $className = get_class($this);
        $involved = $this->getInvolvedClasses($className);

        foreach ($involved as $item) {
            if ($item === $className) {
                continue;
            }

            $dependencies[] = $item;
        }

        return $dependencies;
    }

    /**
     * @return Layer[]
     */
    abstract public function getLayers(): array;

    public function getBaseImage(): string
    {
        $thisClass = get_class($this);
        throw new LogicException(
            "Please override the method getBaseImage in {$thisClass} to return your root image, e.g. 'ubuntu'"
            . " OR override shouldIncludeFromInStage to return false."
        );
    }

    public function getFromLayer(bool $multistage = true): Layer
    {
        $thisClass = get_class($this);
        $parent = get_parent_class($thisClass);

        /** @var Dockerfile&ReflectionClass $reflectedParent */
        $reflectedParent = new ReflectionClass($parent);

        $from = Utils::convertClassNameToStageName($parent);

        if ($reflectedParent->isAbstract()) {
            $from = $this->getBaseImage();
        }

        $as = Utils::convertClassNameToStageName($thisClass);

        if ( ! $multistage) {
            return $this->from($from);
        }

        return $this->from($from, "AS", $as);

    }


    /**
     * @see https://docs.docker.com/engine/reference/builder/#from
     *
     * @param string ...$argument
     *
     * @return Layer
     */
    protected function from(string ...$argument): Layer
    {
        return $this->addInstruction('FROM', ...$argument);
    }

    /**
     * @see https://docs.docker.com/engine/reference/builder/#run
     *
     * @param string ...$argument
     *
     * @return Layer
     */
    protected function run(string ...$argument): Layer
    {
        return $this->addInstruction('RUN', ...$argument);
    }

    /**
     * @see https://docs.docker.com/engine/reference/builder/#cmd
     *
     * @param string ...$argument
     *
     * @return Layer
     */
    protected function cmd(string ...$argument): Layer
    {
        return $this->addInstruction('CMD', ...$argument);
    }

    /**
     * @see https://docs.docker.com/engine/reference/builder/#label
     *
     * @param string ...$argument
     *
     * @return Layer
     */
    protected function label(string ...$argument): Layer
    {
        return $this->addInstruction('LABEL', ...$argument);
    }

    /**
     * @see https://docs.docker.com/engine/reference/builder/#expose
     *
     * @param int $argument
     *
     * @return Layer
     */
    protected function expose(int $argument): Layer
    {
        return $this->addInstruction('EXPOSE', $argument);
    }

    /**
     * @see https://docs.docker.com/engine/reference/builder/#env
     *
     * @param string ...$argument
     *
     * @return Layer
     */
    protected function env(string ...$argument): Layer
    {
        // Need at least two args
        return $this->addInstruction('ENV', ...$argument);
    }

    /**
     * @see https://docs.docker.com/engine/reference/builder/#add
     *
     * @param string ...$argument
     *
     * @return Layer
     */
    protected function add(string ...$argument): Layer
    {
        // Need at least two args
        return $this->addInstruction('ADD', ...$argument);
    }

    /**
     * @see https://docs.docker.com/engine/reference/builder/#copy
     *
     * @param string ...$argument
     *
     * @return Layer
     */
    protected function copy(string ...$argument): Layer
    {
        // Need at least two args
        return $this->addInstruction('COPY', ...$argument);
    }

    /**
     * @param string $class
     * @param string ...$argument
     *
     * @return Layer
     */
    protected function copyFromStage(string $class, string ...$argument): Layer
    {
        if ( ! in_array($class, $this->getDependentStages(), true)) {

            $niceNameMissing = Utils::getShortClassName($class);
            $niceNameThis = Utils::getShortClassName(get_class($this));

            throw new LogicException(
                "To copy from a stage please add {$niceNameMissing}::class to the getDependentStages method in {$niceNameThis}::class"
            );
        }

        return $this->copy('--from=' . Utils::convertClassNameToStageName($class), ...$argument);
    }

    /**
     * @see https://docs.docker.com/engine/reference/builder/#copy
     *
     * @param string ...$argument
     *
     * @return Layer
     */
    protected function entrypoint(string ...$argument): Layer
    {
        return $this->addInstruction('ENTRYPOINT', ...$argument);
    }

    /**
     * @see https://docs.docker.com/engine/reference/builder/#volume
     *
     * @param string ...$argument
     *
     * @return Layer
     */
    protected function volume(string ...$argument): Layer
    {
        return $this->addInstruction('VOLUME', ...$argument);
    }

    /**
     * @see https://docs.docker.com/engine/reference/builder/#user
     *
     * @param string ...$argument
     *
     * @return Layer
     */
    protected function user(string ...$argument): Layer
    {
        return $this->addInstruction('USER', ...$argument);
    }

    /**
     * @see https://docs.docker.com/engine/reference/builder/#workdir
     *
     * @param string ...$argument
     *
     * @return Layer
     */
    protected function workdir(string ...$argument): Layer
    {
        return $this->addInstruction('WORKDIR', ...$argument);
    }

    /**
     * @see https://docs.docker.com/engine/reference/builder/#arg
     *
     * @param string ...$argument
     *
     * @return Layer
     */
    protected function arg(string ...$argument): Layer
    {
        // Need at least two args
        return $this->addInstruction('ARG', ...$argument);
    }

    /**
     * @see https://docs.docker.com/engine/reference/builder/#onbuild
     *
     * @param string ...$argument
     *
     * @return Layer
     */
    protected function onBuild(string ...$argument): Layer
    {
        return $this->addInstruction('ONBUILD', ...$argument);
    }

    /**
     * @see https://docs.docker.com/engine/reference/builder/#stopsignal
     *
     * @param string ...$argument
     *
     * @return Layer
     */
    protected function stopSignal(string ...$argument): Layer
    {
        return $this->addInstruction('STOPSIGNAL', ...$argument);
    }

    /**
     * @see https://docs.docker.com/engine/reference/builder/#healthcheck
     *
     * @param string ...$argument
     *
     * @return Layer
     */
    protected function healthCheck(string ...$argument): Layer
    {
        return $this->addInstruction('ONBUILD', ...$argument);
    }

    /**
     * @see https://docs.docker.com/engine/reference/builder/#shell
     *
     * @param string ...$argument
     *
     * @return Layer
     */
    protected function shell(string ...$argument): Layer
    {
        return $this->addInstruction('SHELL', ...$argument);
    }

    /**
     * @param string $instruction
     * @param string ...$arguments
     *
     * @return Layer
     */
    private function addInstruction(string $instruction, string ...$arguments): Layer
    {
        return new Layer($instruction, ...$arguments);
    }

    public function getDependentStages(): array
    {
        return [];
    }

    /**
     * @param string $className
     *
     * @return array
     */
    protected function getInvolvedClasses(string $className): array
    {
        $hierarchy = $this->getClassHierarchy($className);

        $involvedClasses = [];

        foreach ($hierarchy as $class) {
            $involvedClasses[] = $class;
            $involvedClasses = array_merge($involvedClasses, $this->getDependenciesForClass($class));
        }

        return array_unique($involvedClasses);
    }

    public function shouldIncludeFromInStage(): bool
    {
        return true;
    }

}
