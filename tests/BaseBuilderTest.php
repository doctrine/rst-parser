<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Builder;
use Exception;

use function file_get_contents;
use function shell_exec;

abstract class BaseBuilderTest extends BaseTest
{
    protected Builder $builder;

    abstract protected function getFixturesDirectory(): string;

    protected function setUp(): void
    {
        parent::setUp();
        shell_exec('rm -rf ' . $this->targetFile());
        $this->builder = new Builder($this->configuration);
        $this->configureBuilder($this->builder);
        // build must be executed
        $this->builder->build($this->sourceFile(), $this->targetFile());
    }

    protected function configureBuilder(Builder $builder): void
    {
    }

    protected function sourceFile(string $file = ''): string
    {
        return __DIR__ . '/' . $this->getFixturesDirectory() . '/input/' . $file;
    }

    protected function targetFile(string $file = ''): string
    {
        return __DIR__ . '/' . $this->getFixturesDirectory() . '/output/' . $file;
    }

    /** @throws Exception */
    protected function getFileContents(string $path): string
    {
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new Exception('Could not load file.');
        }

        return $contents;
    }
}
