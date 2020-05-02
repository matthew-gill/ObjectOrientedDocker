<?php

namespace MattGill;

use MattGill\Model\Layer;
use MattGill\Model\LineageStage;
use MattGill\Model\Noop;

abstract class Dockerfile
{
    /**
     * @var Layer[]
     */
    protected $layers = [];

    /**
     * Creates a new Dockerfile instance.
     *
     * @param bool $initialise - should the layers be built automatically on construct?
     */
    final public function __construct(bool $initialise = true)
    {
        if ( ! $initialise) {
            return;
        }

        if ($this instanceof CompositionDockerfile) {
            $this->from(($this->getRootImage()));
        }

        $this->loadLineageAndConfigure($this);
    }

    abstract public function configure(): void;

    /**
     * @return Layer[]
     */
    public function getLayers(): array
    {
        return $this->layers;
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
        $layer = new Layer($instruction, ...$arguments);
        $this->layers[] = $layer;

        return $layer;
    }

    /**
     * @param bool $withComments
     *
     * @return string
     */
    public function compile(bool $withComments = false): string
    {
        $compiled = "";

        foreach ($this->layers as $layer) {
            $compiled .= $layer->compile($withComments) . "\n";
        }

        return trim($compiled);
    }

    public function launch(): void
    {
        // Probably something VERY hacky here.
    }

    /**
     * If your dockerfile class has a FROM or COPY stage which is
     * not already part of your dockerfile's ancestry, return the class
     * here. If the class is not present and missing from the ancestry
     * it is assumed that the container is public and available in your
     * docker context, e.g. ubuntu or busybox.
     *
     * @return array
     */
    protected function getDependentStages(): array
    {
        return [];
    }

    /**
     * The root image which the container is built on, e.g. 'ubuntu' or 'busybox'
     *
     * @return string
     */
    abstract public function getRootImage(): string;

    /**
     * @param Dockerfile $dockerfile
     * @param array      $priorLineage
     */
    private function loadLineageAndConfigure(Dockerfile $dockerfile, array $priorLineage = []): void
    {
        $lineage = [];

        // If this dockerfile has dependencies which aren't in the inheritance structure, we construct them manually and
        // load THEIR lineage too. This triggers recursion in case a dependant class ALSO has dependencies.
        $this->loadDependencies($dockerfile->getDependentStages(), $priorLineage);

        // Because get_parent_class returns the class name as a string. We convert the current dockerfile to it's class
        // string too so we can use it in the loop.
        $currentClass = get_class($dockerfile);

        // Traverse up the inheritance layers for the dockerfile
        while ($parent = get_parent_class($currentClass)) {

            if ($currentClass === ComponentDockerfile::class || $currentClass === CompositionDockerfile::class) {
                break;
            }

            $lineageStage = new LineageStage(new $currentClass(false));

            // We use the stagename to index the array because dependencies may bring in the same stage more than once
            // and we only ever want to build the stage once.
            $lineage[$lineageStage->getStageName()] = $lineageStage;

            $currentClass = $parent;
        }

        // As the above parent lineage starts from child -> parent -> grandparent, this means that if we DONT reverse
        // the order a child container will try to be built before it's parent - and the child class will have
        // dependencies on the parents, so the parents need to be built first: grandparent -> parent -> child.
        $lineage = array_reverse($lineage);

        // Now the lineage is in order, we append it to the existing lineage
        $lineage = array_merge($priorLineage, $lineage);

        // Composed dockerfiles may have dependenciess which come after it, so load those up too.
        if ($dockerfile instanceof CompositionDockerfile) {
            foreach ($dockerfile->getDependentStagesAfter() as $dependency) {
                $lineageStage = new LineageStage(new $dependency(false));
                $lineage[$lineageStage->getStageName()] = $lineageStage;

            }
        }

        // Now we have our array of dependencies, we can compile everything.
        $this->buildMultistageLayers($lineage);
    }

    /**
     * @param LineageStage[] $lineageStages
     */
    private function buildMultistageLayers(array $lineageStages): void
    {
        foreach ($lineageStages as $lineageStage) {

            if ( ! $this instanceof CompositionDockerfile) {
                $this->from("{$lineageStage->getFrom()} as {$lineageStage->getStageName()}");
            }
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $this->layers = array_merge($this->layers, $lineageStage->getLayers());

            $this->layers[] = new Noop();
        }
    }

    /**
     * @param array $dependencies
     * @param array $existingLineage
     */
    private function loadDependencies(array $dependencies, array $existingLineage): void
    {
        foreach ($dependencies as $dependency) {
            /** @var Dockerfile $instanciated */
            $instanciated = new $dependency(false);
            $this->loadLineageAndConfigure($instanciated, $existingLineage);
        }
    }

}
