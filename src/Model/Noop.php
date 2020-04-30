<?php

namespace MattGill\Model;

use LogicException;

/**
 * A layer which is just a blank line. Does nothing.
 * Useful to separate stages to make the result a bit more readable.
 */
class Noop extends Layer
{
    public function __construct()
    {
        parent::__construct('', '');
    }

    /**
     * @param bool $withComments
     *
     * @return string
     */
    public function compile(bool $withComments = false): string
    {
        return '';
    }

    /**
     * @param string $comment
     *
     * @return Layer
     */
    public function setComment(string $comment): Layer
    {
        throw new LogicException("Can't set comment on a noop");
    }

    /**
     * @param bool $multiline
     *
     * @return Layer
     */
    public function setMultiline(bool $multiline = true): Layer
    {
        throw new LogicException("Can't set multiline on a noop");
    }
}
