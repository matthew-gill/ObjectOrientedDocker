<?php

namespace MattGill\Model;

class Layer
{
    /**
     * @var string
     */
    private $instruction;

    /**
     * @var string[]
     */
    private $arguments;

    /**
     * @var string
     */
    private $stageName = null;

    /**
     * @var bool
     */
    private $multiline;
    /**
     * @var string
     */
    private $comment = null;

    /**
     * Layer constructor.
     *
     * @param string $instruction
     * @param string ...$arguments
     */
    public function __construct(string $instruction, string ...$arguments)
    {
        $this->instruction = $instruction;
        $this->arguments = $arguments;
    }

    public function setStageName(string $stageName): Layer
    {
        // Todo does this layer support being the first layer of a stage
        $this->stageName = $stageName;

        return $this;
    }

    public function setMultiline(bool $multiline = true): Layer
    {
        $this->multiline = $multiline;

        return $this;
    }

    public function setComment(string $comment): Layer
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get this layer as a compiled string. e.g "FROM alpine"
     *
     * @param bool $withComments
     *
     * @return string
     */
    public function compile(bool $withComments = false): string
    {
        $compiled = '';

        if ($withComments && $this->comment) {
            $compiled .= '# ' . "{$this->comment}\n";
        }

        $stageSuffix = $this->stageName ? " as {$this->stageName}" : '';

        $arguments = $this->arguments;

        if ($this->multiline) {
            $arguments = [implode(" && \ \n\t", $this->arguments)];
        }

        $compiled .= implode(" ", array_merge([$this->instruction], $arguments)) . $stageSuffix;

        return $compiled;
    }

    /**
     * @return string
     */
    public function getInstruction(): string
    {
        return $this->instruction;
    }

    /**
     * @param string $instruction
     */
    public function setInstruction(string $instruction): void
    {
        $this->instruction = $instruction;
    }

    /**
     * @return string[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @param string ...$arguments
     */
    public function setArguments(string ...$arguments): void
    {
        $this->arguments = $arguments;
    }
}
