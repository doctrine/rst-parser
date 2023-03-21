<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\Builder;

use ArrayIterator;
use Doctrine\RST\Builder\Scanner;
use Doctrine\RST\Meta\DocumentMetaData;
use Doctrine\RST\Meta\Metas;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

use function time;

class ScannerTest extends TestCase
{
    /** @var Finder|MockObject */
    private $finder;

    /** @var Metas|MockObject */
    private $metas;

    /** @var Scanner */
    private $scanner;

    /** @var SplFileInfo[]|MockObject[]|ArrayIterator<string, SplFileInfo> */
    private $fileMocks;

    /** @var DocumentMetaData[]|MockObject[] */
    private $metaEntryMocks = [];

    public function testScanWithNoMetas(): void
    {
        $this->metas->expects(self::any())
            ->method('get')
            ->willReturn(null);

        $this->addFileMockToFinder('file1.rst');
        $this->addFileMockToFinder('file2.rst');
        $this->addFileMockToFinder('file3.rst');
        $this->addFileMockToFinder('subdir/file4.rst');
        $this->addFileMockToFinder('subdir/file5.rst');

        $parseQueue = $this->scanner->scan();
        self::assertSame([
            'file1',
            'file2',
            'file3',
            'subdir/file4',
            'subdir/file5',
        ], $parseQueue->getAllFilesThatRequireParsing());
    }

    public function testScanWithNonFreshMetas(): void
    {
        $file1InfoMock = $this->addFileMockToFinder('file1.rst');
        $file1MetaMock = $this->createMetaEntryMock('file1');

        // file1.rst was modified 50 seconds ago
        $file1InfoMock->method('getMTime')->willReturn(time() - 50);
        // but file1 MetaEntry was modified 100 seconds ago (is out of date)
        $file1MetaMock->method('getMTime')->willReturn(time() - 100);
        // should never be called because the meta is definitely not fresh
        $file1MetaMock->expects(self::never())->method('getDepends');

        $file2InfoMock = $this->addFileMockToFinder('file2.rst');
        $file2MetaMock = $this->createMetaEntryMock('file2');

        // file2.rst was modified 50 seconds ago
        $lastModifiedTime = time() - 50;
        $file2InfoMock->method('getMTime')->willReturn($lastModifiedTime);
        // and file2 MetaEntry was also 50 seconds ago, fresh
        $file2MetaMock->method('getMTime')->willReturn($lastModifiedTime);
        // ignore dependencies for this test
        $file2MetaMock->expects(self::once())
            ->method('getDepends')
            ->willReturn([]);

        $parseQueue = $this->scanner->scan();
        self::assertSame(['file1'], $parseQueue->getAllFilesThatRequireParsing());
        self::assertFalse($parseQueue->doesFileRequireParsing('file2'));
    }

    public function testScanWithDependencies(): void
    {
        /*
         * Here is the dependency tree and results:
         *      * file1 (unmodified)
         *          depends on: file2
         *      * file2 (unmodified)
         *          depends on: file3, file1
         *      * file3 (unmodified)
         *          depends on: file4, file5, file3, file2
         *      * file4 (unmodified)
         *          depends on: nothing
         *      * file5 (MODIFIED)
         *          depends on: file3, file6
         *      * file6 (unmodified)
         *          depends on: file4
         *
         * Result is that the following files are fresh:
         *      * file1
         *      * file2
         *      * file4
         *      * file6
         */

        $metaMTime = time() - 50;

        $file1InfoMock = $this->addFileMockToFinder('file1.rst');
        $file1InfoMock->method('getMTime')->willReturn($metaMTime);
        $file1MetaMock = $this->createMetaEntryMock('file1');
        $file1MetaMock->method('getDepends')
            ->willReturn(['file2']);
        $file1MetaMock->method('getMTime')->willReturn($metaMTime);

        $file2InfoMock = $this->addFileMockToFinder('file2.rst');
        $file2InfoMock->method('getMTime')->willReturn($metaMTime);
        $file2MetaMock = $this->createMetaEntryMock('file2');
        $file2MetaMock->method('getDepends')
            ->willReturn(['file2', 'file3']);
        $file2MetaMock->method('getMTime')->willReturn($metaMTime);

        $file3InfoMock = $this->addFileMockToFinder('file3.rst');
        $file3InfoMock->method('getMTime')->willReturn($metaMTime);
        $file3MetaMock = $this->createMetaEntryMock('file3');
        $file3MetaMock->method('getDepends')
            ->willReturn(['file4', 'file5', 'file3', 'file2']);
        $file3MetaMock->method('getMTime')->willReturn($metaMTime);

        $file4InfoMock = $this->addFileMockToFinder('file4.rst');
        $file4InfoMock->method('getMTime')->willReturn($metaMTime);
        $file4MetaMock = $this->createMetaEntryMock('file4');
        $file4MetaMock->method('getDepends')
            ->willReturn([]);
        $file4MetaMock->method('getMTime')->willReturn($metaMTime);

        $file5InfoMock = $this->addFileMockToFinder('file5.rst');
        // THIS file is the one file that's modified
        $file5InfoMock->method('getMTime')->willReturn(time() - 10);
        $file5MetaMock = $this->createMetaEntryMock('file5');
        $file5MetaMock->method('getDepends')
            ->willReturn(['file3', 'file6']);
        $file5MetaMock->method('getMTime')->willReturn($metaMTime);

        $file6InfoMock = $this->addFileMockToFinder('file6.rst');
        $file6InfoMock->method('getMTime')->willReturn($metaMTime);
        $file6MetaMock = $this->createMetaEntryMock('file6');
        $file6MetaMock->method('getDepends')
            ->willReturn(['file4']);
        $file6MetaMock->method('getMTime')->willReturn($metaMTime);

        $parseQueue = $this->scanner->scan();
        self::assertSame([
            'file3',
            'file5',
        ], $parseQueue->getAllFilesThatRequireParsing());
    }

    public function testScanWithNonExistentDependency(): void
    {
        /*
         *      * file1 (unmodified)
         *          depends on: file2
         *      * file2 (does not exist)
         *          depends on: file3, file1
         *
         * Result is that file 1 DOES need to be parsed
         */

        $metaMTime = time() - 50;

        $file1InfoMock = $this->addFileMockToFinder('file1.rst');
        $file1InfoMock->method('getMTime')->willReturn($metaMTime);
        $file1MetaMock = $this->createMetaEntryMock('file1');
        $file1MetaMock->method('getDepends')
            ->willReturn(['file2']);
        $file1MetaMock->method('getMTime')->willReturn($metaMTime);

        // no file info made for file2

        $parseQueue = $this->scanner->scan();
        self::assertSame(['file1'], $parseQueue->getAllFilesThatRequireParsing());
    }

    protected function setUp(): void
    {
        $this->fileMocks = new ArrayIterator();
        $this->finder    = $this->createMock(Finder::class);
        $this->finder->expects(self::any())
            ->method('getIterator')
            ->willReturn($this->fileMocks);
        $this->finder->expects(self::once())
            ->method('in')
            ->with('/directory')
            ->willReturnSelf();
        $this->finder->expects(self::once())
            ->method('files')
            ->with()
            ->willReturnSelf();
        $this->finder->expects(self::once())
            ->method('name')
            ->with('*.rst')
            ->willReturnSelf();

        $this->metas = $this->createMock(Metas::class);
        $this->metas->expects(self::any())
            ->method('get')
            ->willReturnCallback(fn ($filename) => $this->metaEntryMocks[$filename] ?? null);

        $this->scanner = new Scanner('rst', '/directory', $this->metas, $this->finder);
    }

    /** @return MockObject|SplFileInfo */
    private function addFileMockToFinder(string $relativePath)
    {
        $fileInfo = $this->createMock(SplFileInfo::class);
        $fileInfo->expects(self::any())
            ->method('getRelativePathname')
            ->willReturn($relativePath);

        $this->fileMocks[$relativePath] = $fileInfo;

        return $fileInfo;
    }

    /** @return MockObject|DocumentMetaData */
    private function createMetaEntryMock(string $filename)
    {
        $meta = $this->createMock(DocumentMetaData::class);

        $this->metaEntryMocks[$filename] = $meta;

        return $meta;
    }
}
