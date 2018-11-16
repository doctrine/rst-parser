<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\Builder;

use Doctrine\RST\Builder\Documents;
use Doctrine\RST\Builder\ParseQueue;
use Doctrine\RST\Builder\ParseQueueProcessor;
use Doctrine\RST\Builder\Scanner;
use Doctrine\RST\ErrorManager;
use Doctrine\RST\Kernel;
use Doctrine\RST\Meta\Metas;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function sys_get_temp_dir;
use function touch;

class ParseQueueProcessorTest extends TestCase
{
    /** @var Kernel|MockObject */
    private $kernel;

    /** @var ErrorManager|MockObject */
    private $errorManager;

    /** @var ParseQueue|MockObject */
    private $parseQueue;

    /** @var Metas|MockObject */
    private $metas;

    /** @var Documents|MockObject */
    private $documents;

    /** @var Scanner|MockObject */
    private $scanner;

    /** @var string */
    private $directory;

    /** @var string */
    private $targetDirectory;

    /** @var string */
    private $fileExtension;

    /** @var ParseQueueProcessor */
    private $parseQueueProcessor;

    public function testProcess() : void
    {
        touch($this->directory . '/file.rst');

        $this->parseQueue->expects(self::at(0))
            ->method('getFileToParse')
            ->willReturn('file');

        $this->parseQueue->expects(self::at(1))
            ->method('getFileToParse')
            ->willReturn(null);

        $this->documents->expects(self::once())
            ->method('addDocument')
            ->with('file');

        $this->kernel->expects(self::once())
            ->method('postParse');

        $this->metas->expects(self::once())
            ->method('set');

        $this->parseQueueProcessor->process();
    }

    protected function setUp() : void
    {
        $this->kernel          = $this->createMock(Kernel::class);
        $this->errorManager    = $this->createMock(ErrorManager::class);
        $this->parseQueue      = $this->createMock(ParseQueue::class);
        $this->metas           = $this->createMock(Metas::class);
        $this->documents       = $this->createMock(Documents::class);
        $this->scanner         = $this->createMock(Scanner::class);
        $this->directory       = sys_get_temp_dir();
        $this->targetDirectory = '/target';
        $this->fileExtension   = 'rst';

        $this->parseQueueProcessor = new ParseQueueProcessor(
            $this->kernel,
            $this->errorManager,
            $this->parseQueue,
            $this->metas,
            $this->documents,
            $this->scanner,
            $this->directory,
            $this->targetDirectory,
            $this->fileExtension
        );
    }
}
