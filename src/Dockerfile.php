<?php

namespace MattGill;

use MattGill\Model\Layer;

abstract class Dockerfile
{
    /**
     * @var Layer[]
     */
    protected $layers;

    /**
     * @var array<string,<int>
     */
    protected $instructionCountMap;

    public function __construct()
    {
        $this->configure();
    }

    abstract protected function configure(): void;

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
        // Array is preferred
        /**
         * The CMD instruction has three forms:
         * CMD ["executable","param1","param2"] (exec form, this is the preferred form)
         * CMD ["param1","param2"] (as default parameters to ENTRYPOINT)
         * CMD command param1 param2 (shell form)
         */
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
        // Array is preferred
        /**
         * The CMD instruction has three forms:
         * CMD ["executable","param1","param2"] (exec form, this is the preferred form)
         * CMD ["param1","param2"] (as default parameters to ENTRYPOINT)
         * CMD command param1 param2 (shell form)
         */
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

        $this->incrementInstructionCount($instruction);

        return $layer;
    }

    /**
     * @param bool $withComments
     *
     * @return string
     */
    public function compile(bool $withComments = false): string
    {
        $compiled = '';

        foreach ($this->layers as $layer) {
            $compiled .= $layer->compile($withComments) . "\n" . "\n";
        }

        return $compiled;
    }

    /**
     * @param string $instruction
     */
    private function incrementInstructionCount(string $instruction): void
    {
        // todo validate invalid instructions haven't been added
        $currentValue = $this->instructionCountMap[$instruction] ?? 0;

        $this->instructionCountMap[$instruction] = ++$currentValue;
    }

    public function launch(): void
    {
        // Probably something VERY hacky here.
    }

    abstract protected function getImageName(): string;

    abstract protected function getTag(): string;

}
