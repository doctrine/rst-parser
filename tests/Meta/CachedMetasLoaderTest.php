<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\Meta;

use Doctrine\RST\ErrorManager;
use Doctrine\RST\Meta\CachedMetasLoader;
use Doctrine\RST\Meta\LinkTarget;
use Doctrine\RST\Meta\MetaEntry;
use Doctrine\RST\Meta\Metas;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function sys_get_temp_dir;

class CachedMetasLoaderTest extends TestCase
{
    /** @var ErrorManager|MockObject */
    private ErrorManager $errorManager;

    protected function setUp(): void
    {
        $this->errorManager = $this->createMock(ErrorManager::class);
    }

    public function testSaveAndLoadCachedMetaEntries(): void
    {
        $targetDir = sys_get_temp_dir();
        $meta1     = new MetaEntry('file1', 'url1', 'title1', [], [], [], [], 0);
        $meta2     = new MetaEntry('file2', 'url2', 'title2', [], [], [], [], 0);

        $metasBefore = new Metas($this->errorManager, [$meta1, $meta2]);
        $metasAfter  = new Metas($this->errorManager);

        $loader = new CachedMetasLoader();
        $loader->cacheMetaEntries($targetDir, $metasBefore);
        $loader->loadCachedMetaEntries($targetDir, $metasAfter);
        self::assertEquals([$meta1, $meta2], $metasAfter->getAll());
    }

    public function testSaveAndLoadCachedLinkTargets(): void
    {
        $targetDir  = sys_get_temp_dir();
        $linkEntry1 = new LinkTarget('name1', 'url1', 'Some Title');
        $linkEntry2 = new LinkTarget('name2', 'url2');

        $metasBefore = new Metas($this->errorManager, [], ['name1' => $linkEntry1, 'name2' => $linkEntry2]);
        $metasAfter  = new Metas($this->errorManager);

        $loader = new CachedMetasLoader();
        $loader->cacheMetaEntries($targetDir, $metasBefore);
        $loader->loadCachedMetaEntries($targetDir, $metasAfter);
        self::assertEquals(['name1' => $linkEntry1, 'name2' => $linkEntry2], $metasAfter->getLinkTargets());
    }
}
