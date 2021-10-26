<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\BuilderReferences;

use Doctrine\RST\Builder;
use Doctrine\Tests\RST\BaseBuilderTest;
use Gajus\Dindent\Indenter;
use Symfony\Component\Finder\Finder;

use function rtrim;

class BuilderReferencesTest extends BaseBuilderTest
{
    protected function configureBuilder(Builder $builder): void
    {
        $builder->getConfiguration()->abortOnError(false);
        $builder->getConfiguration()->silentOnError();
    }

    public function test(): void
    {
        $indenter = new Indenter();

        foreach (Finder::create()->files()->in($this->targetFile() . '/../expected') as $file) {
            $target = $this->targetFile($file->getRelativePathname());
            self::assertFileExists($target);
            self::assertEquals(rtrim($file->getContents()), rtrim($indenter->indent($this->getFileContents($target))));
        }
    }

    protected function getFixturesDirectory(): string
    {
        return 'BuilderReferences';
    }
}
