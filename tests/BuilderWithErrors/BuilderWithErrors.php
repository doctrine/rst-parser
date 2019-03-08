<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\BuilderWithErrors;

use Doctrine\RST\Builder;
use Doctrine\Tests\RST\BaseBuilderTest;

class BuilderWithErrors extends BaseBuilderTest
{
    protected function configureBuilder(Builder $builder) : void
    {
        $builder->getConfiguration()->abortOnError(false);
    }

    public function testMalformedTable() : void
    {
        $contents = $this->getFileContents($this->targetFile('index.html'));
        self::assertContains('<table', $contents);
        self::assertNotContains('<tr', $contents);
    }

    protected function getFixturesDirectory() : string
    {
        return 'BuilderWithErrors';
    }
}
