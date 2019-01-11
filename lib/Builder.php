<?php

declare(strict_types=1);

namespace Doctrine\RST;

use Doctrine\RST\Builder\Copier;
use Doctrine\RST\Builder\Documents;
use Doctrine\RST\Builder\ParseQueue;
use Doctrine\RST\Builder\ParseQueueProcessor;
use Doctrine\RST\Builder\Scanner;
use Doctrine\RST\Event\PostBuildRenderEvent;
use Doctrine\RST\Event\PreBuildParseEvent;
use Doctrine\RST\Event\PreBuildRenderEvent;
use Doctrine\RST\Event\PreBuildScanEvent;
use Doctrine\RST\Meta\MetaEntry;
use Doctrine\RST\Meta\Metas;
use Symfony\Component\Filesystem\Filesystem;
use function is_dir;

class Builder
{
    /** @var Kernel */
    private $kernel;

    /** @var Configuration */
    private $configuration;

    /** @var ErrorManager */
    private $errorManager;

    /** @var Filesystem */
    private $filesystem;

    /** @var Metas */
    private $metas;

    /** @var Documents */
    private $documents;

    /** @var ParseQueue */
    private $parseQueue;

    /** @var Scanner */
    private $scanner;

    /** @var Copier */
    private $copier;

    /** @var string */
    private $indexName = 'index';

    public function __construct(?Kernel $kernel = null)
    {
        $this->kernel = $kernel ?? new Kernel();

        $this->configuration = $this->kernel->getConfiguration();

        $this->errorManager = new ErrorManager($this->configuration);

        $this->filesystem = new Filesystem();

        $this->metas = new Metas();

        $this->documents = new Builder\Documents(
            $this->filesystem,
            $this->metas
        );

        $this->parseQueue = new Builder\ParseQueue($this->documents);

        $this->scanner = new Builder\Scanner($this->parseQueue, $this->metas);

        $this->copier = new Builder\Copier($this->filesystem);

        $this->kernel->initBuilder($this);
    }

    public function recreate() : Builder
    {
        return new Builder($this->kernel);
    }

    public function getKernel() : Kernel
    {
        return $this->kernel;
    }

    public function getConfiguration() : Configuration
    {
        return $this->configuration;
    }

    public function getDocuments() : Documents
    {
        return $this->documents;
    }

    public function getParseQueue() : Builder\ParseQueue
    {
        return $this->parseQueue;
    }

    public function getErrorManager() : ErrorManager
    {
        return $this->errorManager;
    }

    public function setIndexName(string $name) : self
    {
        $this->indexName = $name;

        return $this;
    }

    public function getIndexName() : string
    {
        return $this->indexName;
    }

    public function build(
        string $directory,
        string $targetDirectory = 'output'
    ) : void {
        // Creating output directory if doesn't exists
        if (! is_dir($targetDirectory)) {
            $this->filesystem->mkdir($targetDirectory, 0755);
        }

        $this->loadCachedMetas($targetDirectory);

        $this->scan($directory, $targetDirectory);

        $this->parse($directory, $targetDirectory);

        $this->render($directory, $targetDirectory);

        $this->saveMetas($targetDirectory);
    }

    public function copy(string $source, ?string $destination = null) : self
    {
        $this->copier->copy($source, $destination);

        return $this;
    }

    public function mkdir(string $directory) : self
    {
        $this->copier->mkdir($directory);

        return $this;
    }

    private function scan(string $directory, string $targetDirectory) : void
    {
        $this->configuration->dispatchEvent(
            PreBuildScanEvent::PRE_BUILD_SCAN,
            new PreBuildScanEvent($this, $directory, $targetDirectory)
        );

        $this->scanner->scan($directory, $this->getIndexName());

        $this->scanner->scanMetas($directory);
    }

    private function parse(string $directory, string $targetDirectory) : void
    {
        $this->configuration->dispatchEvent(
            PreBuildParseEvent::PRE_BUILD_PARSE,
            new PreBuildParseEvent($this, $directory, $targetDirectory)
        );

        $parseQueueProcessor = new ParseQueueProcessor(
            $this->kernel,
            $this->errorManager,
            $this->parseQueue,
            $this->metas,
            $this->documents,
            $this->scanner,
            $directory,
            $targetDirectory,
            $this->configuration->getFileExtension()
        );

        $parseQueueProcessor->process();
    }

    private function render(string $directory, string $targetDirectory) : void
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

    private function loadCachedMetas(string $targetDirectory) : void
    {
        $metaCachePath = $this->getMetaCachePath($targetDirectory);
        if (!file_exists($metaCachePath)) {
            return;
        }

        $this->metas->setMetaEntries(unserialize(file_get_contents($metaCachePath)));
    }

    private function saveMetas(string $targetDirectory) : void
    {
        file_put_contents($this->getMetaCachePath($targetDirectory), serialize($this->metas->getAll()));
    }

    private function getMetaCachePath(string $targetDirectory) : string
    {
        return $targetDirectory.'/metas.php';
    }
}
