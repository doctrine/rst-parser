<?php

declare(strict_types=1);

namespace Doctrine\RST\Builder;

use Doctrine\RST\Environment;
use Doctrine\RST\ErrorManager;
use Doctrine\RST\Kernel;
use Doctrine\RST\Meta\Metas;
use Doctrine\RST\Nodes\DocumentNode;
use Doctrine\RST\Parser;
use function array_filter;
use function file_exists;
use function filectime;

class ParseQueueProcessor
{
    /** @var Kernel */
    private $kernel;

    /** @var ErrorManager */
    private $errorManager;

    /** @var ParseQueue */
    private $parseQueue;

    /** @var Metas */
    private $metas;

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
        ErrorManager $errorManager,
        ParseQueue $parseQueue,
        Metas $metas,
        Documents $documents,
        Scanner $scanner,
        string $directory,
        string $targetDirectory,
        string $fileExtension
    ) {
        $this->kernel          = $kernel;
        $this->errorManager    = $errorManager;
        $this->parseQueue      = $parseQueue;
        $this->metas           = $metas;
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

        $document = $parser->parseFile($fileAbsolutePath);

        $this->documents->addDocument($file, $document);

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
        $environment = new Environment(
            $this->kernel->getConfiguration(),
            $file,
            $this->metas,
            $this->directory,
            $this->targetDirectory,
            $this->errorManager
        );
        $parser = new Parser($this->kernel, $environment);

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

    private function buildDocumentUrl(DocumentNode $document) : string
    {
        return $document->getEnvironment()->getUrl() . '.' . $this->fileExtension;
    }
}
