<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Configuration;
use Doctrine\RST\ErrorManager;
use PHPUnit\Framework\TestCase;

class ErrorManagerTest extends TestCase
{
    public function testGetErrors(): void
    {
        $configuration = $this->createMock(Configuration::class);
        $configuration->expects(self::atLeastOnce())
            ->method('isAbortOnError')
            ->willReturn(false);

        $errorManager = new ErrorManager($configuration);
        $errorManager->error('ERROR FOO');
        $errorManager->error('ERROR BAR');
        self::assertSame(['ERROR FOO', 'ERROR BAR'], $errorManager->getErrors());
    }
}
