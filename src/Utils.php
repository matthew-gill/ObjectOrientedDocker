<?php

namespace MattGill;

class Utils
{
    /**
     * Converts a class name to lower case and replaces slashes with dashes
     * e.g.
     *
     * An\Example\Class => an-example-class
     *
     * Note that this is designed to have Something::class to be passed to it as a variable. As such special characters
     * will likely have undesired effects.
     *
     * @param string $className
     *
     * @return string
     */
    public static function convertClassNameToStageName(string $className): string
    {
        return str_replace('\\', '-', strtolower($className));
    }

    public static function getShortClassName(string $className): string
    {
        $pieces = explode('\\', $className);

        return array_pop($pieces);
    }
}
