<?php

declare(strict_types=1);

namespace Doctrine\RST\Builder;

use Doctrine\RST\Configuration;
use Doctrine\RST\Event\PostProcessFileEvent;
use Doctrine\RST\Meta\DocumentMetaData;
use Doctrine\RST\Meta\Metas;
use Doctrine\RST\Nodes\DocumentNode;
use Doctrine\RST\Parser;

use function filemtime;
use function fwrite;
use function getenv;
use function sprintf;

use const PHP_SAPI;
use const STDERR;

final class ParseQueueProcessor
{
    private Configuration $configuration;

    private Metas $metas;

    private Documents $documents;

    private string $directory;

    private string $targetDirectory;

    private string $fileExtension;

    public function __construct(
        Configuration $configuration,
        Metas $metas,
        Documents $documents,
        string $directory,
        string $targetDirectory,
        string $fileExtension
    ) {
        $this->configuration   = $configuration;
        $this->metas           = $metas;
        $this->documents       = $documents;
        $this->directory       = $directory;
        $this->targetDirectory = $targetDirectory;
        $this->fileExtension   = $fileExtension;
    }

    public function process(ParseQueue $parseQueue): void
    {
        foreach ($parseQueue->getAllFilesThatRequireParsing() as $file) {
            $this->processFile($file);
        }
    }

    private function processFile(string $file): void
    {
        if (getenv('SHELL_VERBOSITY') >= 1 && PHP_SAPI === 'cli') {
            fwrite(STDERR, sprintf("Processing file: %s\n", $file));
        }

        $fileAbsolutePath = $this->buildFileAbsolutePath($file);

        $parser = $this->createFileParser($file);

        $environment = $parser->getEnvironment();

        $documentNode = $parser->parseFile($fileAbsolutePath);

        $this->documents->addDocument($file, $documentNode);

        $documentMetaData = new DocumentMetaData(
            $file,
            $this->buildDocumentUrl($documentNode),
            (string) $documentNode->getTitle(),
            $documentNode->getTitles(),
            $documentNode->getTocs(),
            $environment->getDependencies(),
            $environment->getLinkTargets(),
            (int) filemtime($fileAbsolutePath)
        );

        $this->configuration->getEventManager()->dispatchEvent(
            PostProcessFileEvent::POST_PROCESS_FILE,
            new PostProcessFileEvent($this->configuration, $documentNode)
        );

        $this->metas->set($documentMetaData);
    }

    private function createFileParser(string $file): Parser
    {
        $parser = new Parser($this->configuration);

        $environment = $parser->getEnvironment();
        $environment->setMetas($this->metas);
        $environment->setCurrentFileName($file);
        $environment->setCurrentDirectory($this->directory);
        $environment->setTargetDirectory($this->targetDirectory);

        return $parser;
    }

    private function buildFileAbsolutePath(string $file): string
    {
        return $this->directory . '/' . $file . '.rst';
    }

    private function buildDocumentUrl(DocumentNode $document): string
    {
        return $document->getEnvironment()->getUrl() . '.' . $this->fileExtension;
    }
}
