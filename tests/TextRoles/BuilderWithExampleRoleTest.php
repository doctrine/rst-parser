<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\TextRoles;

use Doctrine\RST\Builder;
use Doctrine\RST\Directives\CustomDirectiveFactory;
use Doctrine\Tests\RST\BaseBuilderTest;

use function file_exists;

/**
 * Unit testing for RST
 */
class BuilderWithExampleRoleTest extends BaseBuilderTest
{
    protected function configureBuilder(Builder $builder): void
    {
        $this->configuration->addDirectiveFactory(new CustomDirectiveFactory(
            [],
            [new ExampleRole()]
        ));
    }

    public function testDirectiveFactoryRegistersTextRole(): void
    {
        self::assertTrue(file_exists($this->targetFile('index.html')));

        $contents = $this->getFileContents($this->targetFile('index.html'));

        self::assertStringContainsString('<samp>Some example</samp>', $contents);
    }

    protected function getFixturesDirectory(): string
    {
        return 'TextRoles';
    }
}
