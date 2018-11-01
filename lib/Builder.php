<?php

declare(strict_types=1);

namespace Doctrine\RST;

use Doctrine\RST\Builder\Copier;
use Doctrine\RST\Builder\Documents;
use Doctrine\RST\Builder\Hooks;
use Doctrine\RST\Builder\ParseQueue;
use Doctrine\RST\Builder\ParseQueueProcessor;
use Doctrine\RST\Builder\Scanner;
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

    /** @var Hooks */
    private $hooks;

    /** @var Copier */
    private $copier;

    /** @var string */
    private $indexName = 'index';

    public function __construct(?Kernel $kernel = null, ?Configuration $configuration = null)
    {
        $this->kernel = $kernel ?? new HTML\Kernel();

        $this->configuration = $configuration ?? new Configuration();

        $this->errorManager = new ErrorManager($this->configuration);

        $this->filesystem = new Filesystem();

        $this->metas = new Metas();

        $this->documents = new Builder\Documents($this->filesystem, $this->metas);

        $this->parseQueue = new Builder\ParseQueue($this->documents);

        $this->scanner = new Builder\Scanner($this->parseQueue, $this->metas);

        $this->hooks = new Builder\Hooks();

        $this->copier = new Builder\Copier($this->filesystem);

        $this->kernel->initBuilder($this);
    }

    public function getDocuments() : Documents
    {
        return $this->documents;
    }

    public function getErrorManager() : ErrorManager
    {
        return $this->errorManager;
    }

    public function addHook(callable $function) : self
    {
        $this->hooks->addHook($function);

        return $this;
    }

    public function addBeforeHook(callable $function) : self
    {
        $this->hooks->addBeforeHook($function);

        return $this;
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

    public function setUseRelativeUrls(bool $useRelativeUrls) : void
    {
        $this->configuration->setUseRelativeUrls($useRelativeUrls);
    }

    public function build(
        string $directory,
        string $targetDirectory = 'output'
    ) : void {
        // Creating output directory if doesn't exists
        if (! is_dir($targetDirectory)) {
            $this->filesystem->mkdir($targetDirectory, 0755);
        }

        $this->scan($directory);

        $this->parse($directory, $targetDirectory);

        $this->render($directory, $targetDirectory);
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

    private function scan(string $directory) : void
    {
        $this->scanner->scan($directory, $this->getIndexName());

        $this->scanner->scanMetas($directory);
    }

    private function parse(string $directory, string $targetDirectory) : void
    {
        $parseQueueProcessor = new ParseQueueProcessor(
            $this->kernel,
            $this->configuration,
            $this->errorManager,
            $this->parseQueue,
            $this->metas,
            $this->hooks,
            $this->documents,
            $this->scanner,
            $directory,
            $targetDirectory,
            $this->kernel->getFileExtension()
        );

        $parseQueueProcessor->process();
    }

    private function render(string $directory, string $targetDirectory) : void
    {
        $this->documents->render($targetDirectory);

        $this->copier->doMkdir($targetDirectory);
        $this->copier->doCopy($directory, $targetDirectory);
    }
}
