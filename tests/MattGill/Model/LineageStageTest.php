<?php

namespace MattGill\Model;

use MattGill\Examples\Test\SimpleExtendedTestDockerfile;
use MattGill\Examples\Test\TestDockerfile;
use MattGill\Utils;
use PHPUnit\Framework\TestCase;

class LineageStageTest extends TestCase
{
    public function testItGetsTagNameForRootImages(): void
    {
        $testFile = new TestDockerfile();

        $lineage = new LineageStage($testFile);

        $this->assertSame(
            'the-root-image:some-tag',
            $lineage->getFrom()
        );
    }


    public function testItUsesParentTagNameForExtendingDockerfile(): void
    {
        $testFile = new SimpleExtendedTestDockerfile();

        $lineage = new LineageStage($testFile);

        $this->assertSame(
            Utils::convertClassNameToStageName(TestDockerfile::class),
            $lineage->getFrom()
        );
    }

    public function testGetLayersReturnCorrectCountAndContent(): void
    {
        $testFile = new TestDockerfile(false);

        $lineage = new LineageStage($testFile);

        $layers = $lineage->getLayers();

        $this->assertCount(4, $layers);

    }

    public function testGetStageName(): void
    {
        $testFile = new TestDockerfile(false);

        $lineage = new LineageStage($testFile);

        $this->assertSame(
            Utils::convertClassNameToStageName(get_class($testFile)),
            $lineage->getStageName()
        );
    }

}
