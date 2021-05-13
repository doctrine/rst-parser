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
        $configuration->expects(self::atLeastOnce())
            ->method('isSilentOnError')
            ->willReturn(true);

        $errorManager = new ErrorManager($configuration);
        $errorManager->error('ERROR FOO');
        $errorManager->error('ERROR BAR');

        $errors = $errorManager->getErrors();
        self::assertSame('ERROR FOO', $errors[0]->asString());
        self::assertSame('ERROR BAR', $errors[1]->asString());
    }
}
