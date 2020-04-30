<?php

namespace MattGill;

use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    /**
     * @dataProvider provideClassNameToStageName
     *
     * @param string $input
     * @param string $expected
     */
    public function testConvertClassNameToStageName(string $input, string $expected): void
    {
        $this->assertSame(
            $expected,
            Utils::convertClassNameToStageName($input)
        );
    }

    public function provideClassNameToStageName(): array
    {
        return [
            "normal"                              => [
                "normal",
                "normal",
            ],
            "UPPER CASE to lower case"            => [
                "CHANGECASE",
                "changecase",
            ],
            "Test slashes get replaced to dashes" => [
                "Slashes\\Between\\Things",
                "slashes-between-things",
            ],
            // There will be loads of strings that don't work in the currrent implementation. These tests currently just
            // test the existing use of SomeClass::class being passed as an argument.
        ];
    }
}
