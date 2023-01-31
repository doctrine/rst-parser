<?php

declare(strict_types=1);

namespace Doctrine\RST\Builder;

use Doctrine\RST\Meta\Metas;
use Doctrine\RST\Nodes\DocumentNode;
use InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;

use function dirname;
use function is_dir;
use function sprintf;

class Documents
{
    private Filesystem $filesystem;

    private Metas $metas;

    /** @var DocumentNode[] */
    private array $documents = [];

    public function __construct(
        Filesystem $filesystem,
        Metas $metas
    ) {
        $this->filesystem = $filesystem;
        $this->metas      = $metas;
    }

    /** @return DocumentNode[] */
    public function getAll(): array
    {
        return $this->documents;
    }

    public function hasDocument(string $file): bool
    {
        return isset($this->documents[$file]);
    }

    public function addDocument(string $file, DocumentNode $document): void
    {
        $this->documents[$file] = $document;
    }

    public function render(string $targetDirectory): void
    {
        foreach ($this->documents as $file => $document) {
            $target = $this->getTargetOf($targetDirectory, $file);

            $directory = dirname($target);

            if (! is_dir($directory)) {
                $this->filesystem->mkdir($directory, 0755);
            }

            $this->filesystem->dumpFile($target, $document->renderDocument());
        }
    }

    private function getTargetOf(string $targetDirectory, string $file): string
    {
        $metaEntry = $this->metas->get($file);

        if ($metaEntry === null) {
            throw new InvalidArgumentException(sprintf('Could not find target file for %s', $file));
        }

        return $targetDirectory . '/' . $metaEntry->getUrl();
    }
}
