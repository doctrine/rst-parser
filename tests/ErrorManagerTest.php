<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Configuration;
use Doctrine\RST\ErrorManager;
use PHPUnit\Framework\TestCase;
use function ob_end_clean;
use function ob_start;

class ErrorManagerTest extends TestCase
{
    public function testGetErrors(): void
    {
        $configuration = $this->createMock(Configuration::class);
        $configuration->expects(self::atLeastOnce())
            ->method('isAbortOnError')
            ->willReturn(false);

        $errorManager = new ErrorManager($configuration);
        ob_start();
        $errorManager->error('ERROR FOO');
        $errorManager->error('ERROR BAR');
        ob_end_clean();
        self::assertSame(['ERROR FOO', 'ERROR BAR'], $errorManager->getErrors());
    }
}
