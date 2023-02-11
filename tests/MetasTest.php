<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Configuration;
use Doctrine\RST\ErrorManager;
use Doctrine\RST\ErrorManager\ErrorManagerFactory;
use Doctrine\RST\Meta\LinkTarget;
use Doctrine\RST\Meta\DocumentMetaData;
use Doctrine\RST\Meta\Metas;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MetasTest extends TestCase
{
    private Configuration $configuration;
    /** @var ErrorManager|MockObject */
    private ErrorManager $errorManager;

    protected function setUp(): void
    {
        $this->configuration = new Configuration();
        $this->configuration->setUseCachedMetas(false);
        $this->errorManager  = $this->createMock(ErrorManager::class);
        $errorManagerFactory = $this->createMock(ErrorManagerFactory::class);
        $this->configuration->setErrorManagerFactory($errorManagerFactory);
        $errorManagerFactory->method('getErrorManager')->willReturn($this->errorManager);
        $this->errorManager->expects(self::never())->method('warning');
        $this->errorManager->expects(self::never())->method('error');
    }

    public function testFindLinkMetaEntry(): void
    {
        $entry1 = new DocumentMetaData(
            'test.rst',
            'test.html',
            'Test',
            [],
            [],
            [],
            [
                'link1' => new LinkTarget('link1', '/link1'),
                'link2' => new LinkTarget('link2', '/link2'),
            ],
            0
        );

        $entry2 = new DocumentMetaData(
            'test.rst',
            'test.html',
            'Test',
            [],
            [],
            [],
            [
                'link3' => new LinkTarget('link3', '/link3'),
                'link4' => new LinkTarget('link4', '/link4'),
            ],
            0
        );

        $metas = new Metas(
            $this->configuration,
            [
                $entry1,
                $entry2,
            ]
        );

        self::assertSame($entry1, $metas->findLinkTargetMetaEntry('link1'));
        self::assertSame($entry1, $metas->findLinkTargetMetaEntry('link2'));
        self::assertSame($entry2, $metas->findLinkTargetMetaEntry('link3'));
        self::assertSame($entry2, $metas->findLinkTargetMetaEntry('link4'));
    }
}
