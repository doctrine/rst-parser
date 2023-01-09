<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\TextRoles;

use Doctrine\RST\Configuration;
use Doctrine\RST\Environment;
use Doctrine\RST\ErrorManager;
use Doctrine\RST\ErrorManager\ErrorManagerFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EnvironmentTest extends TestCase
{
    private Environment $environment;
    /** @var ErrorManager|MockObject */
    private $errorManager;

    protected function setUp(): void
    {
        $configuration       = new Configuration();
        $errorManagerFactory = $this->createMock(ErrorManagerFactory::class);
        $this->errorManager  = $this->createMock(ErrorManager::class);
        $configuration->setErrorManagerFactory($errorManagerFactory);
        $errorManagerFactory->method('getErrorManager')->willReturn($this->errorManager);
        $this->environment = new Environment($configuration);
    }

    public function testRegisterTextRole(): void
    {
        $role = new ExampleRole();
        $this->errorManager->expects(self::never())->method('warning');
        $this->errorManager->expects(self::never())->method('error');
        $this->environment->registerTextRole($role);
        self:self::assertEquals($role, $this->environment->getTextRole('example'));
    }

    public function testUnkownTextRole(): void
    {
        $this->errorManager->expects(self::atLeastOnce())->method('error');
        self:self::assertNull($this->environment->getTextRole('unkown'));
    }
}
