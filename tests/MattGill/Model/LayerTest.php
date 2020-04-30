<?php

namespace MattGill\Model;

use PHPUnit\Framework\TestCase;

class LayerTest extends TestCase
{
    /**
     * @dataProvider provideCommentAndCompilationArgument
     *
     * @param string|null $comment
     * @param bool        $compileWithComments
     * @param string      $expectedOutput
     */
    public function testSetCommentsVisibleWhenCompiledWithComments(
        ?string $comment,
        bool $compileWithComments,
        string $expectedOutput
    ): void {
        $layer = new Layer('INSTRUCTION', 'ARG1', 'ARG2', 'ARG3');

        if (null !== $comment) {
            $layer->setComment($comment);
        }

        $this->assertSame($expectedOutput, $layer->compile($compileWithComments));
    }

    /**
     * @dataProvider provideMultilineArgument
     *
     * @param bool   $enabled
     * @param string $expectedOutput
     * @param string ...$arguments
     */
    public function testSetMultiline(bool $enabled, string $expectedOutput, string ...$arguments): void
    {
        $layer = new Layer('INSTRUCTION', ...$arguments);
        $layer->setMultiline($enabled);

        $this->assertSame($expectedOutput, $layer->compile());
    }

    /**
     * Various configurations to test the setComment method.
     *
     * @return array|array[]
     */
    public function provideCommentAndCompilationArgument(): array
    {
        return [
            "No comment set so nothing visible (comments enabled)"  => [
                // Comment
                null,
                // Compile with comments
                true,
                // Expected output
                'INSTRUCTION ARG1 ARG2 ARG3',
            ],
            "No comment set so nothing visible (comments disabled)" => [
                // Comment
                null,
                // Compile with comments
                false,
                // Expected output
                "INSTRUCTION ARG1 ARG2 ARG3",
            ],
            "Comment set with comments enabled"                     => [
                // Comment
                'Hello from PHPUnit',
                // Compile with comments
                true,
                // Expected output
                "# Hello from PHPUnit\n" .
                "INSTRUCTION ARG1 ARG2 ARG3",
            ],
            "Comment set with comments disabled"                    => [
                // Comment
                'Hello from PHPUnit',
                // Compile with comments
                false,
                // Expected output
                "INSTRUCTION ARG1 ARG2 ARG3",
            ],
        ];
    }

    /**
     * Various configurations to test the setMultiline method.
     *
     * @return array|array[]
     */
    public function provideMultilineArgument(): array
    {
        return [
            "Enabled with only one instruction (no change)"  => [
                true,
                "INSTRUCTION hello from tests",
                "hello from tests",
            ],
            "Disabled with only one instruction (no change)" => [
                false,
                "INSTRUCTION hello from tests",
                "hello from tests",
            ],
            "Enabled with multiple instructions"             => [
                true,
                <<<EXPECTED
INSTRUCTION multiple && \ 
    instructions && \ 
    multiple && \ 
    lines
EXPECTED
                ,
                "multiple",
                "instructions",
                "multiple",
                "lines",
            ],
            "Disabled with multiple instructions"            => [
                false,
                "INSTRUCTION multiple instructions same line",
                "multiple",
                "instructions",
                "same",
                "line",
            ],
        ];
    }
}
