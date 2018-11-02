<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\Builder;

use Doctrine\RST\Builder\Documents;
use Doctrine\RST\Document;
use Doctrine\RST\ErrorManager;
use Doctrine\RST\MetaEntry;
use Doctrine\RST\Metas;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class DocumentsTest extends TestCase
{
    /** @var ErrorManager|MockObject */
    private $errorManager;

    /** @var Filesystem|MockObject */
    private $filesystem;

    /** @var Metas|MockObject */
    private $metas;

    /** @var Documents */
    private $documents;

    public function testGetAll() : void
    {
        $document1 = $this->createMock(Document::class);
        $document2 = $this->createMock(Document::class);

        $this->documents->addDocument('document1', $document1);
        $this->documents->addDocument('document2', $document2);

        $expected = [
            'document1' => $document1,
            'document2' => $document2,
        ];

        self::assertSame($expected, $this->documents->getAll());
    }

    public function testHasDocument() : void
    {
        self::assertFalse($this->documents->hasDocument('document'));

        $document = $this->createMock(Document::class);

        $this->documents->addDocument('document', $document);

        self::assertTrue($this->documents->hasDocument('document'));
    }

    public function testRender() : void
    {
        $document = $this->createMock(Document::class);

        $this->documents->addDocument('document', $document);

        $metaEntry = $this->createMock(MetaEntry::class);

        $this->metas->expects(self::once())
            ->method('get')
            ->with('document')
            ->willReturn($metaEntry);

        $metaEntry->expects(self::once())
            ->method('getUrl')
            ->willReturn('url');

        $this->filesystem->expects(self::once())
            ->method('mkdir')
            ->with('/target', 0755);

        $document->expects(self::once())
            ->method('renderDocument')
            ->willReturn('rendered document');

        $this->filesystem->expects(self::once())
            ->method('dumpFile')
            ->with('/target/url', 'rendered document');

        $this->documents->render('/target');
    }

    protected function setUp() : void
    {
        $this->errorManager = $this->createMock(ErrorManager::class);
        $this->filesystem   = $this->createMock(Filesystem::class);
        $this->metas        = $this->createMock(Metas::class);

        $this->documents = new Documents($this->errorManager, $this->filesystem, $this->metas);
    }
}
