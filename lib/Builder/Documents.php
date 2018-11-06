<?php

declare(strict_types=1);

namespace Doctrine\RST\Builder;

use Doctrine\RST\Configuration;
use Doctrine\RST\Document;
use Doctrine\RST\ErrorManager;
use Doctrine\RST\Metas;
use InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;
use function dirname;
use function is_dir;
use function sprintf;

class Documents
{
    /** @var Configuration */
    private $configuration;

    /** @var ErrorManager */
    private $errorManager;

    /** @var Filesystem */
    private $filesystem;

    /** @var Metas */
    private $metas;

    /** @var Document[] */
    private $documents = [];

    public function __construct(
        Configuration $configuration,
        ErrorManager $errorManager,
        Filesystem $filesystem,
        Metas $metas
    ) {
        $this->configuration = $configuration;
        $this->errorManager  = $errorManager;
        $this->filesystem    = $filesystem;
        $this->metas         = $metas;
    }

    /**
     * @return Document[]
     */
    public function getAll() : array
    {
        return $this->documents;
    }

    public function hasDocument(string $file) : bool
    {
        return isset($this->documents[$file]);
    }

    public function addDocument(string $file, Document $document) : void
    {
        $this->documents[$file] = $document;
    }

    public function render(string $targetDirectory) : void
    {
        foreach ($this->documents as $file => $document) {
            $target = $this->getTargetOf($targetDirectory, $file);

            $directory = dirname($target);

            if (! is_dir($directory)) {
                $this->filesystem->mkdir($directory, 0755);
            }

            $renderedDocument = $document->renderDocument();

            if ($this->configuration->getIgnoreInvalidReferences() === false) {
                foreach ($document->getInvalidReferences() as $invalidReference) {
                    $this->errorManager->error(sprintf(
                        'Found invalid reference "%s" in file "%s"',
                        $invalidReference->getUrl(),
                        $file
                    ));
                }
            }

            $this->filesystem->dumpFile($target, $renderedDocument);
        }
    }

    private function getTargetOf(string $targetDirectory, string $file) : string
    {
        $metaEntry = $this->metas->get($file);

        if ($metaEntry === null) {
            throw new InvalidArgumentException(sprintf('Could not find target file for %s', $file));
        }

        return $targetDirectory . '/' . $metaEntry->getUrl();
    }
}
