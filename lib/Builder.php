<?php

declare(strict_types=1);

namespace Doctrine\RST;

use Doctrine\RST\Builder\Copier;
use Doctrine\RST\Builder\Documents;
use Doctrine\RST\Builder\ParseQueue;
use Doctrine\RST\Builder\ParseQueueProcessor;
use Doctrine\RST\Builder\Scanner;
use Doctrine\RST\Event\PostBuilderInitEvent;
use Doctrine\RST\Event\PostBuildRenderEvent;
use Doctrine\RST\Event\PreBuildParseEvent;
use Doctrine\RST\Event\PreBuildRenderEvent;
use Doctrine\RST\Event\PreBuildScanEvent;
use Doctrine\RST\Meta\CachedMetasLoader;
use Doctrine\RST\Meta\Metas;
use InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

use function file_exists;
use function is_dir;
use function sprintf;

/**
 * Builds a complete manual or book containing of multiple `.rst` documents
 * and additional sources.
 *
 * Usage:
 *
 * .. code-block:: php
 *
 *    $builder = new Builder($configuration, $kernel);
 *    $builder->build('Documentation', 'output');
 */
final class Builder
{
    /** @var Kernel */
    private $kernel;

    private Configuration $configuration;

    /** @var Filesystem */
    private $filesystem;

    /** @var Metas */
    private $metas;

    /** @var CachedMetasLoader */
    private $cachedMetasLoader;

    /** @var Documents */
    private $documents;

    /** @var Copier */
    private $copier;

    /** @var Finder|null */
    private $scannerFinder;

    public function __construct(Configuration $configuration, ?Kernel $kernel = null)
    {
        $this->configuration = $configuration;

        $this->kernel = $kernel ?? new Kernel($this->configuration);

        $this->filesystem = new Filesystem();

        $this->metas = new Metas($this->configuration);

        $this->cachedMetasLoader = new CachedMetasLoader();

        $this->documents = new Builder\Documents(
            $this->filesystem,
            $this->metas
        );

        $this->copier = new Builder\Copier($this->filesystem);

        $this->configuration->dispatchEvent(
            PostBuilderInitEvent::POST_BUILDER_INIT,
            new PostBuilderInitEvent($this->configuration, $this)
        );
    }

    /**
     * Main method to build `.rst` files from the $directory into the
     * $targetDirectory. Output format and other settings are used from the
     * Configuration passed to the Builders constructor.
     */
    public function build(
        string $directory,
        string $targetDirectory = 'output'
    ): void {
        // Creating output directory if doesn't exists
        if (! is_dir($targetDirectory)) {
            $this->filesystem->mkdir($targetDirectory, 0755);
        }

        $indexFilename = $this->getConfiguration()->getIndexFileName();
        if (! file_exists($directory . '/' . $indexFilename)) {
            throw new InvalidArgumentException(sprintf('Could not find index file "%s" in "%s"', $indexFilename, $directory));
        }

        if ($this->configuration->getUseCachedMetas()) {
            $this->cachedMetasLoader->loadCachedMetaEntries($targetDirectory, $this->metas);
        }

        $parseQueue = $this->scan($directory, $targetDirectory);

        $this->parse($directory, $targetDirectory, $parseQueue);

        $this->render($directory, $targetDirectory);

        $this->cachedMetasLoader->cacheMetaEntries($targetDirectory, $this->metas);
    }

    public function recreate(): Builder
    {
        return new Builder($this->configuration, $this->kernel);
    }

    public function getKernel(): Kernel
    {
        return $this->kernel;
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function getDocuments(): Documents
    {
        return $this->documents;
    }

    public function getMetas(): Metas
    {
        return $this->metas;
    }

    public function copy(string $source, ?string $destination = null): self
    {
        $this->copier->copy($source, $destination);

        return $this;
    }

    public function mkdir(string $directory): self
    {
        $this->copier->mkdir($directory);

        return $this;
    }

    /**
     * Set the Finder that will be used for scanning files.
     */
    public function setScannerFinder(Finder $finder): void
    {
        $this->scannerFinder = $finder;
    }

    private function scan(string $directory, string $targetDirectory): ParseQueue
    {
        $this->configuration->dispatchEvent(
            PreBuildScanEvent::PRE_BUILD_SCAN,
            new PreBuildScanEvent($this, $directory, $targetDirectory)
        );

        $scanner = new Scanner(
            $this->configuration->getSourceFileExtension(),
            $directory,
            $this->metas,
            $this->getScannerFinder()
        );

        return $scanner->scan();
    }

    private function parse(string $directory, string $targetDirectory, ParseQueue $parseQueue): void
    {
        $this->configuration->dispatchEvent(
            PreBuildParseEvent::PRE_BUILD_PARSE,
            new PreBuildParseEvent($this, $directory, $targetDirectory, $parseQueue)
        );

        $parseQueueProcessor = new ParseQueueProcessor(
            $this->configuration,
            $this->kernel,
            $this->metas,
            $this->documents,
            $directory,
            $targetDirectory,
            $this->configuration->getFileExtension()
        );

        $parseQueueProcessor->process($parseQueue);
    }

    private function render(string $directory, string $targetDirectory): void
    {
        $this->configuration->dispatchEvent(
            PreBuildRenderEvent::PRE_BUILD_RENDER,
            new PreBuildRenderEvent($this, $directory, $targetDirectory)
        );

        $this->documents->render($targetDirectory);

        $this->copier->doMkdir($targetDirectory);
        $this->copier->doCopy($directory, $targetDirectory);

        $this->configuration->dispatchEvent(
            PostBuildRenderEvent::POST_BUILD_RENDER,
            new PostBuildRenderEvent($this, $directory, $targetDirectory)
        );
    }

    private function getScannerFinder(): Finder
    {
        if ($this->scannerFinder === null) {
            $this->scannerFinder = new Finder();
        }

        return $this->scannerFinder;
    }
}
