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
     * @var bool
     */
    private $multiline;
    /**
     * @var string
     */
    private $comment;

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

    /**
     * @param bool $multiline
     *
     * @return $this
     */
    public function setMultiline(bool $multiline = true): Layer
    {
        $this->multiline = $multiline;

        return $this;
    }

    /**
     * @param string $comment
     *
     * @return $this
     */
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

        $arguments = $this->arguments;

        if ($this->multiline) {
            $arguments = [implode(" && \ \n    ", $this->arguments)];
        }

        $compiled .= implode(" ", array_merge([$this->instruction], $arguments));

        return $compiled;
    }
}
