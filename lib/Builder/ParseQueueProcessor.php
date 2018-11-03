<?php

declare(strict_types=1);

namespace Doctrine\RST\Builder;

use Doctrine\RST\Configuration;
use Doctrine\RST\Document;
use Doctrine\RST\ErrorManager;
use Doctrine\RST\Kernel;
use Doctrine\RST\Metas;
use Doctrine\RST\Parser;
use function array_filter;
use function file_exists;
use function filectime;

class ParseQueueProcessor
{
    /** @var Kernel */
    private $kernel;

    /** @var Configuration */
    private $configuration;

    /** @var ErrorManager */
    private $errorManager;

    /** @var ParseQueue */
    private $parseQueue;

    /** @var Metas */
    private $metas;

    /** @var Hooks */
    private $hooks;

    /** @var Documents */
    private $documents;

    /** @var Scanner */
    private $scanner;

    /** @var string */
    private $directory;

    /** @var string */
    private $targetDirectory;

    /** @var string */
    private $fileExtension;

    public function __construct(
        Kernel $kernel,
        Configuration $configuration,
        ErrorManager $errorManager,
        ParseQueue $parseQueue,
        Metas $metas,
        Hooks $hooks,
        Documents $documents,
        Scanner $scanner,
        string $directory,
        string $targetDirectory,
        string $fileExtension
    ) {
        $this->kernel          = $kernel;
        $this->configuration   = $configuration;
        $this->errorManager    = $errorManager;
        $this->parseQueue      = $parseQueue;
        $this->metas           = $metas;
        $this->hooks           = $hooks;
        $this->documents       = $documents;
        $this->scanner         = $scanner;
        $this->directory       = $directory;
        $this->targetDirectory = $targetDirectory;
        $this->fileExtension   = $fileExtension;
    }

    public function process() : void
    {
        while ($file = $this->parseQueue->getFileToParse()) {
            $this->processFile($file);
        }
    }

    private function processFile(string $file) : void
    {
        $fileAbsolutePath = $this->buildFileAbsolutePath($file);

        $parser = $this->createFileParser($file);

        $environment = $parser->getEnvironment();

        $this->hooks->callBeforeHooks($parser);

        $document = $parser->parseFile($fileAbsolutePath);

        $this->documents->addDocument($file, $document);

        $this->hooks->callHooks($document);

        $this->kernel->postParse($document);

        $dependencies = $environment->getDependencies();

        foreach ($this->buildDependenciesToScan($dependencies) as $dependency) {
            $this->scanner->scan($this->directory, $dependency);
        }

        $this->metas->set(
            $file,
            $this->buildDocumentUrl($document),
            (string) $document->getTitle(),
            $document->getTitles(),
            $document->getTocs(),
            (int) filectime($fileAbsolutePath),
            $dependencies,
            $environment->getLinks()
        );
    }

    private function createFileParser(string $file) : Parser
    {
        $parser = new Parser(null, $this->kernel, $this->configuration);

        $environment = $parser->getEnvironment();
        $environment->setMetas($this->metas);
        $environment->setCurrentFileName($file);
        $environment->setCurrentDirectory($this->directory);
        $environment->setTargetDirectory($this->targetDirectory);
        $environment->setErrorManager($this->errorManager);

        return $parser;
    }

    /**
     * @param string[] $dependencies
     *
     * @return string[]
     */
    private function buildDependenciesToScan(array $dependencies) : array
    {
        return array_filter($dependencies, function (string $dependency) : bool {
            return file_exists($this->buildFileAbsolutePath($dependency));
        });
    }

    private function buildFileAbsolutePath(string $file) : string
    {
        return $this->directory . '/' . $file . '.rst';
    }

    private function buildDocumentUrl(Document $document) : string
    {
        return $document->getEnvironment()->getUrl() . '.' . $this->fileExtension;
    }
}
