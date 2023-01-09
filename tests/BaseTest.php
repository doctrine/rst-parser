<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Configuration;
use Doctrine\RST\ErrorManager;
use Doctrine\RST\ErrorManager\ErrorManagerFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class BaseTest extends TestCase
{
    protected Configuration $configuration;
    /** @var ErrorManager|MockObject */
    protected ErrorManager $errorManager;

    protected function setUp(): void
    {
        $this->configuration = new Configuration();
        $this->configuration->setUseCachedMetas(false);
        $this->errorManager  = $this->createMock(ErrorManager::class);
        $errorManagerFactory = $this->createMock(ErrorManagerFactory::class);
        $this->configuration->setErrorManagerFactory($errorManagerFactory);
        $errorManagerFactory->method('getErrorManager')->willReturn($this->errorManager);
        $this->configureExpectedWarnings();
        $this->configureExpectedErrors();
    }

    /**
     * We expect no warnings to occur during the build. If you want to test
     * expected warnings to be found, override this method.
     */
    protected function configureExpectedWarnings(): void
    {
        $this->errorManager->expects(self::never())->method('warning');
    }

    /**
     * We expect no errors to occur during the build. If you want to test
     * expected errors to be found, override this method.
     */
    protected function configureExpectedErrors(): void
    {
        $this->errorManager->expects(self::never())->method('error');
    }
}
