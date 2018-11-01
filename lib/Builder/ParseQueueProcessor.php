<?php

declare(strict_types=1);

namespace Doctrine\RST\Builder;

use Doctrine\RST\Configuration;
use Doctrine\RST\Document;
use Doctrine\RST\ErrorManager;
use Doctrine\RST\Kernel;
use Doctrine\RST\Metas;
use Doctrine\RST\Parser;
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
            // Process the file
            $rst = $this->directory . '/' . $file . '.rst';

            if (! file_exists($rst)) {
                continue;
            }

            $parser = new Parser(null, $this->kernel, $this->configuration);

            $environment = $parser->getEnvironment();
            $environment->setMetas($this->metas);
            $environment->setCurrentFileName($file);
            $environment->setCurrentDirectory($this->directory);
            $environment->setTargetDirectory($this->targetDirectory);
            $environment->setErrorManager($this->errorManager);
            $environment->setUseRelativeUrls($this->configuration->useRelativeUrls());

            $this->hooks->callBeforeHooks($parser);

            $document = $parser->parseFile($rst);

            $this->documents->addDocument($file, $document);

            // Calling all the post-process hooks
            $this->hooks->callHooks($document);

            // Calling the kernel document tweaking
            $this->kernel->postParse($document);

            $dependencies = $document->getEnvironment()->getDependencies();

            if ($dependencies !== []) {
                // Scan the dependencies for this document
                foreach ($dependencies as $dependency) {
                    $this->scanner->scan($this->directory, $dependency);
                }
            }

            // Append the meta for this document
            $this->metas->set(
                $file,
                $this->getUrl($document),
                (string) $document->getTitle(),
                $document->getTitles(),
                $document->getTocs(),
                (int) filectime($rst),
                $dependencies,
                $environment->getLinks()
            );
        }
    }

    private function getUrl(Document $document) : string
    {
        return $document->getEnvironment()->getUrl() . '.' . $this->fileExtension;
    }
}
