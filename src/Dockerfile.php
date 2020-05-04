<?php

namespace MattGill;

use LogicException;
use MattGill\Model\Layer;
use MattGill\Model\Noop;
use Pustato\TopSort\Collection;
use Pustato\TopSort\Contracts\Sortable;
use Pustato\TopSort\Exceptions\InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

abstract class Dockerfile implements Sortable
{
    /**
     * Return the compiled dockerfile for the definition provided.s
     *
     * @param bool $withComments
     *
     * @return string
     */
    public function compile(bool $withComments = false): string
    {
        $layers = $this->compileStageLayers();

        $compiled = '';

        foreach ($layers as $layer) {
            $compiled .= $layer->compile($withComments) . "\n";
        }

        return trim($compiled);
    }

    /**
     * Returns an array of all the layers that make up the components.
     *
     * @return Layer[]
     */
    protected function compileStageLayers(): array
    {
        $topologicallySorted = $this->sortTopologically();

        $layers = [];

        foreach ($topologicallySorted as $dockerfile) {

            // Optionally add the FROM layer.
            if ($this->shouldAutomaticallyAddFromStage()) {
                $layers[] = $dockerfile->getFromLayer();
            }

            // Add the layers for this dockerfile
            $layers = array_merge($layers, $dockerfile->getLayers());
            $layers[] = new Noop();
        }

        return $layers;
    }

    /**
     * Given a class name, determine if it has dependent classes via the {@see Dockerfile::getDependentStages} method.
     * If it does, use recursion to find their lineage and if they have any other dependant stages too.
     *
     * @param string $className
     *
     * @return array
     * @throws ReflectionException
     */
    protected function getDependenciesForClass(string $className): array
    {
        $reflected = new ReflectionClass($className);
        $getDependentStagesMethod = $reflected->getMethod('getDependentStages');

        // If the class in question implements getDependentStages (i.e. it's not inherited), then there are no
        // dependenciees.
        if ($getDependentStagesMethod->class !== $className) {
            return [];
        }

        // This class DOES implement getDependentStages directly.

        /** @var Dockerfile $instanciated */
        $instanciated = new $className();

        $dependencies = [];

        // For each dependency load the dependency's class lineage and it's dependency (recursively).
        foreach ($instanciated->getDependentStages() as $dependency) {
            /** @var Dockerfile $instanciatedDependency */
            $dependencies = array_merge($dependencies, $this->getClassAncestry($dependency));
            $dependencies = array_merge($dependencies, $this->getDependenciesForClass($dependency));
        }

        return $dependencies;
    }

    /**
     * Move up this dockerfile's ancestry, returning an array of the classes it inherits from.
     *
     * @param string $context
     *
     * @return array
     * @throws ReflectionException
     */
    protected function getClassAncestry(string $context): array
    {
        $hierarchy = [];

        while ($context) {
            $reflected = new ReflectionClass($context);

            // We don't care about abstract classes.
            if ($reflected->isAbstract()) {
                break;
            }

            $hierarchy[] = $context;

            $context = get_parent_class($context);
        }

        return $hierarchy;
    }

    /**
     * Define the array of layers, in order. These are the dockerfile's instructions, e.g. RUN, COPY, ENTRYPOINT.
     *
     * @return Layer[]
     */
    abstract protected function getLayers(): array;

    /**
     * The image which this is built on top of, e.g. ubuntu.
     *
     * @return string
     */
    protected function getBaseImage(): string
    {
        $thisClass = get_class($this);
        throw new LogicException(
            "Please override the method getBaseImage in {$thisClass} to return your root image, e.g. 'ubuntu'"
            . " OR override shouldIncludeFromInStage to return false."
        );
    }

    /**
     * Return a layer which specifies the FROM stage for the current dockerfile.
     *
     * @param bool $multistage
     *
     * @return Layer
     * @throws ReflectionException
     */
    protected function getFromLayer(bool $multistage = true): Layer
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
     * Adds a copy layer but copies content from an external dependency.
     *
     * @param string $dependencyClass
     * @param string ...$argument
     *
     * @return Layer
     */
    protected function copyFromStage(string $dependencyClass, string ...$argument): Layer
    {
        if ( ! in_array($dependencyClass, $this->getDependentStages(), true)) {

            $niceNameMissing = Utils::getShortClassName($dependencyClass);
            $niceNameThis = Utils::getShortClassName(get_class($this));

            throw new LogicException(
                "To copy from a stage please add {$niceNameMissing}::class to the getDependentStages method in {$niceNameThis}::class"
            );
        }

        return $this->copy('--from=' . Utils::convertClassNameToStageName($dependencyClass), ...$argument);
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

    /**
     * An array of dockerfile classnames on which this dockerfile depends on. Allows them to be used in a
     * {@see Dockerfile::copyFromStage()} call.
     *
     * @return array
     */
    protected function getDependentStages(): array
    {
        return [];
    }

    /**
     * Return class hierarchy and external dependencies linked to the specified class name.
     *
     * @param string $className
     *
     * @return array
     */
    private function getInvolvedClasses(string $className): array
    {
        $hierarchy = $this->getClassAncestry($className);

        $involvedClasses = [];

        foreach ($hierarchy as $class) {
            $involvedClasses[] = $class;
            $involvedClasses = array_merge($involvedClasses, $this->getDependenciesForClass($class));
        }

        return array_unique($involvedClasses);
    }

    /**
     * @return bool
     */
    protected function shouldAutomaticallyAddFromStage(): bool
    {
        return true;
    }


    /**
     * Used by {@see Pustato} to define the class ID (in this case, just the class name). Used to topologically sort.
     *
     * @return string
     */
    public function getId(): string
    {
        return static::class;
    }

    /**
     * Used by {@see Pustato} to perform topological sort on this dockerfile's dependencies.
     *
     * @return array
     */
    public function getDependenciesIds(): array
    {
        $className = get_class($this);

        // Return everything except the instance of this class.
        return array_filter(
            $this->getInvolvedClasses($className),
            static function (string $class) use ($className) {
                return $class !== $className;
            }
        );
    }

    /**
     * Uses a topological sort to get the related dockerfile classes in dependency order,
     *
     * @return Dockerfile[]
     * @throws InvalidArgumentException
     */
    private function sortTopologically(): array
    {
        // Get lineage and dependent classes for this dockerfile.
        $classes = $this->getInvolvedClasses(get_class($this));

        $instanciated = [];

        // Instanciate each one.
        foreach ($classes as $class) {
            $instanciated[] = new $class();
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $assetsCollection = new Collection($instanciated);

        // This order defines the required order in which each stage should be created so as to ensure all dependencies
        // are met.
        return $assetsCollection->getSorted();
    }
}
