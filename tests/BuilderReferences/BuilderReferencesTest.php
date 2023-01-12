<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\BuilderReferences;

use Doctrine\Tests\RST\BaseBuilderTest;
use Gajus\Dindent\Indenter;
use Symfony\Component\Finder\Finder;

use function rtrim;

class BuilderReferencesTest extends BaseBuilderTest
{
    protected function configureExpectedErrors(): void
    {
        $this->errorManager->expects(self::atLeastOnce())
            ->method('error')
            ->with('Found invalid reference "Some Sub Section"', 'index', null, null);
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
