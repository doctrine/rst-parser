<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\Builder;

use Doctrine\RST\Builder\Copier;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class CopierTest extends TestCase
{
    /** @var Filesystem|MockObject */
    private $filesystem;

    /** @var Copier */
    private $copier;

    public function testDoCopy(): void
    {
        $this->filesystem->expects(self::once())
            ->method('copy')
            ->with('/source/from', '/target/to');

        $this->copier->copy('from', 'to');

        $this->copier->doCopy('/source', '/target');
    }

    public function testDoMkdir(): void
    {
        $this->filesystem->expects(self::once())
            ->method('mkdir')
            ->with('/target/directory');

        $this->copier->mkdir('directory');

        $this->copier->doMkdir('/target');
    }

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);

        $this->copier = new Copier($this->filesystem);
    }
}
