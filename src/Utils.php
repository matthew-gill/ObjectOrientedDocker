<?php

namespace MattGill;

class Utils
{
    public static function sluggifyClassName(string $className): string
    {
        return str_replace('\\', '-', strtolower($className));
    }
}
