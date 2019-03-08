<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\BuilderInvalidLinks;

use Doctrine\RST\Builder;
use Doctrine\RST\Meta\MetaEntry;
use Doctrine\Tests\RST\BaseBuilderTest;
use function file_get_contents;
use function unserialize;

class BuilderInvalidLinksTest extends BaseBuilderTest
{
    protected function configureBuilder(Builder $builder) : void
    {
        $builder->getConfiguration()->abortOnError(false);
    }

    public function testInvalidLinksRemovedFromMetas() : void
    {
        $cachedContents = (string) file_get_contents($this->targetFile('metas.php'));
        /** @var MetaEntry[] $metaEntries */
        $metaEntries = unserialize($cachedContents);
        // will only depend on "good" because other links are invalid
        self::assertCount(1, $metaEntries['subdir/foo']->getDepends());
    }

    protected function getFixturesDirectory() : string
    {
        return 'BuilderInvalidLinks';
    }
}
