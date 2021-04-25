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
    /**
     * @group legacy
     */
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

    /**
     * Make sure the method is unchanged when addError() is used.
     *
     * @group legacy
     */
    public function testGetErrorsWithNewErrorObject(): void
    {
        $configuration = $this->createMock(Configuration::class);
        $configuration->expects(self::atLeastOnce())
            ->method('isAbortOnError')
            ->willReturn(false);
        $configuration->expects(self::atLeastOnce())
            ->method('isSilentOnError')
            ->willReturn(true);

        $errorManager = new ErrorManager($configuration);
        $errorManager->addError('ERROR FOO');
        $errorManager->addError('ERROR BAR');

        self::assertSame(['ERROR FOO', 'ERROR BAR'], $errorManager->getErrors());
    }

    public function testGetAllErrors(): void
    {
        $configuration = $this->createMock(Configuration::class);
        $configuration->expects(self::atLeastOnce())
            ->method('isAbortOnError')
            ->willReturn(false);
        $configuration->expects(self::atLeastOnce())
            ->method('isSilentOnError')
            ->willReturn(true);

        $errorManager = new ErrorManager($configuration);
        $errorManager->addError('ERROR FOO');
        $errorManager->addError('ERROR BAR');

        $errors = $errorManager->getAllErrors();
        self::assertSame('ERROR FOO', $errors[0]->asString());
        self::assertSame('ERROR BAR', $errors[1]->asString());
    }
}
