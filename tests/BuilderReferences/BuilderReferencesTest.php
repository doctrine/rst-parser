<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\BuilderReferences;

use Doctrine\Tests\RST\BaseBuilderTest;
use Symfony\Component\Finder\Finder;

use function array_map;
use function explode;
use function implode;
use function preg_replace;

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
        foreach (Finder::create()->files()->in($this->targetFile() . '/../expected') as $file) {
            $target = $this->targetFile($file->getRelativePathname());
            self::assertFileExists($target);
            self::assertEquals($this->trimLines($file->getContents()), $this->trimLines($this->getFileContents($target)));
        }
    }

    private function trimLines(string $html): string
    {
        $html = implode("\n", array_map('trim', explode("\n", $html)));
        $html = preg_replace('#\\n+#', "\n", $html);

        return $html;
    }

    protected function getFixturesDirectory(): string
    {
        return 'BuilderReferences';
    }
}
