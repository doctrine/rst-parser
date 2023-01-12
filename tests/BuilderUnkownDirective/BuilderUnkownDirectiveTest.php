<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\BuilderDuplicateAnchors;

use Doctrine\RST\Builder;
use Doctrine\RST\Configuration;
use Doctrine\RST\ErrorManager;
use Doctrine\RST\Kernel;
use Doctrine\Tests\RST\BaseBuilderTest;
use PHPUnit\Framework\MockObject\MockObject;

class BuilderUnkownDirectiveTest extends BaseBuilderTest
{
    private Configuration $configuration;

    /** @var ErrorManager|MockObject */
    private $errorManager;

    protected function setUp(): void
    {
        $this->configuration = new Configuration();
        $this->configuration->setUseCachedMetas(false);

        $this->errorManager = $this->createMock(ErrorManager::class);
        $this->builder      = new Builder(new Kernel($this->configuration), $this->errorManager);
    }

    public function testUnkownDirectiveWithFieldOptions(): void
    {
        $this->errorManager->expects(self::atLeastOnce())->method('warning');
        $this->builder->build($this->sourceFile(), $this->targetFile());
    }

    protected function getFixturesDirectory(): string
    {
        return 'BuilderUnkownDirective';
    }
}
