<?php

namespace MattGill\Model;

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

    public function compile(bool $withComments = false): string
    {
        return '';
    }
}
