<?php

declare(strict_types=1);

namespace Doctrine\RST;

use InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;
use function array_shift;
use function basename;
use function dirname;
use function file_exists;
use function filectime;
use function is_dir;
use function sprintf;

class Builder
{
    public const NO_PARSE = 1;
    public const PARSE    = 2;

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

    /** @var string */
    private $indexName = 'index';

    /** @var bool */
    private $verbose = true;

    /** @var string[][] */
    private $toCopy = [];

    /** @var string[] */
    private $toMkdir = [];

    /** @var string */
    private $directory;

    /** @var string */
    private $targetDirectory;

    /** @var int[] */
    private $states = [];

    /** @var string[] */
    private $parseQueue = [];

    /** @var Document[] */
    private $documents = [];

    /** @var callable[] */
    private $beforeHooks = [];

    /** @var callable[] */
    private $hooks = [];

    public function __construct(?Kernel $kernel = null, ?Configuration $configuration = null)
    {
        $this->kernel = $kernel ?? new HTML\Kernel();

        $this->configuration = $configuration ?? new Configuration();

        $this->errorManager = new ErrorManager($this->configuration);

        $this->filesystem = new Filesystem();

        $this->metas = new Metas();

        $this->kernel->initBuilder($this);
    }

    public function recreate() : Builder
    {
        return new Builder($this->kernel);
    }

    /**
     * @return Document[]
     */
    public function getDocuments() : array
    {
        return $this->documents;
    }

    public function getErrorManager() : ErrorManager
    {
        return $this->errorManager;
    }

    public function addHook(callable $function) : self
    {
        $this->hooks[] = $function;

        return $this;
    }

    public function addBeforeHook(callable $function) : self
    {
        $this->beforeHooks[] = $function;

        return $this;
    }

    public function build(string $directory, string $targetDirectory = 'output', bool $verbose = true) : void
    {
        $this->verbose         = $verbose;
        $this->directory       = $directory;
        $this->targetDirectory = $targetDirectory;

        // Creating output directory if doesn't exists
        if (! is_dir($targetDirectory)) {
            $this->filesystem->mkdir($targetDirectory, 0755);
        }

        // Try to load metas, if it does not exists, create it
        $this->display('* Loading metas');

        $this->metas = new Metas();

        // Scan all the metas and the index
        $this->display('* Pre-scanning files');
        $this->scan($this->getIndexName());
        $this->scanMetas();

        // Parses all the documents
        $this->parseAll();

        // Renders all the documents
        $this->render();

        // Copy the files
        $this->display('* Running the copies');
        $this->doMkdir();
        $this->doCopy();
    }

    public function scan(string $file) : void
    {
        // If no decision is already made about this file
        if (isset($this->states[$file])) {
            return;
        }

        $this->display(' -> Scanning ' . $file . '...');
        $this->states[$file] = self::NO_PARSE;
        $entry               = $this->metas->get($file);
        $rst                 = $this->getRST($file);

        if ($entry === null || ! file_exists($rst) || $entry->getCtime() < filectime($rst)) {
            // File was never seen or changed and thus need to be parsed
            $this->addToParseQueue($file);
        } else {
            // Have a look to the file dependencies to knoww if you need to parse
            // it or not
            $depends = $entry->getDepends();

            if ($entry->getParent() !== null) {
                $depends[] = $entry->getParent();
            }

            foreach ($depends as $dependency) {
                $this->scan($dependency);

                // If any dependency needs to be parsed, this file needs also to be
                // parsed
                if ($this->states[$dependency] !== self::PARSE) {
                    continue;
                }

                $this->addToParseQueue($file);
            }
        }
    }

    public function scanMetas() : void
    {
        $entries = $this->metas->getAll();

        foreach ($entries as $file => $infos) {
            $this->scan($file);
        }
    }

    public function getRST(string $file) : string
    {
        return $this->getSourceFile($file . '.rst');
    }

    public function getTargetOf(string $file) : string
    {
        $metaEntry = $this->metas->get($file);

        if ($metaEntry === null) {
            throw new InvalidArgumentException(sprintf('Could not find target file for %s', $file));
        }

        return $this->getTargetFile($metaEntry->getUrl());
    }

    public function getUrl(Document $document) : string
    {
        $environment = $document->getEnvironment();

        return $environment->getUrl() . '.' . $this->kernel->getFileExtension();
    }

    public function getTargetFile(string $filename) : string
    {
        return $this->targetDirectory . '/' . $filename;
    }

    public function getSourceFile(string $filename) : string
    {
        return $this->directory . '/' . $filename;
    }

    public function doCopy() : void
    {
        foreach ($this->toCopy as $copy) {
            [$source, $destination] = $copy;

            if ($source[0] !== '/') {
                $source = $this->getSourceFile($source);
            }

            $destination = $this->getTargetFile($destination);

            if (is_dir($source) && is_dir($destination)) {
                $destination = dirname($destination);
            }

            if (is_dir($source)) {
                $this->filesystem->mirror($source, $destination);
            } else {
                $this->filesystem->copy($source, $destination);
            }
        }
    }

    public function copy(string $source, ?string $destination = null) : self
    {
        if ($destination === null) {
            $destination = basename($source);
        }

        $this->toCopy[] = [$source, $destination];

        return $this;
    }

    public function doMkdir() : void
    {
        foreach ($this->toMkdir as $mkdir) {
            $dir = $this->getTargetFile($mkdir);

            if (is_dir($dir)) {
                continue;
            }

            $this->filesystem->mkdir($dir, 0755);
        }
    }

    public function mkdir(string $directory) : self
    {
        $this->toMkdir[] = $directory;

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

    private function display(string $text) : void
    {
        if (! $this->verbose) {
            return;
        }

        echo $text . "\n";
    }

    private function render() : void
    {
        $this->display('* Rendering documents');

        foreach ($this->documents as $file => &$document) {
            $this->display(' -> Rendering ' . $file . '...');
            $target = $this->getTargetOf($file);

            $directory = dirname($target);

            if (! is_dir($directory)) {
                $this->filesystem->mkdir($directory, 0755);
            }

            $this->filesystem->dumpFile($target, $document->renderDocument());
        }
    }

    private function addToParseQueue(string $file) : void
    {
        $this->states[$file] = self::PARSE;

        if (isset($this->documents[$file])) {
            return;
        }

        $this->parseQueue[$file] = $file;
    }

    private function getFileToParse() : ?string
    {
        if ($this->parseQueue !== []) {
            return array_shift($this->parseQueue);
        }

        return null;
    }

    private function parseAll() : void
    {
        $this->display('* Parsing files');

        while ($file = $this->getFileToParse()) {
            $this->display(' -> Parsing ' . $file . '...');
            // Process the file
            $rst = $this->getRST($file);

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

            foreach ($this->beforeHooks as $hook) {
                $hook($parser);
            }

            $document = $this->documents[$file] = $parser->parseFile($rst);

            // Calling all the post-process hooks
            foreach ($this->hooks as $hook) {
                $hook($document);
            }

            // Calling the kernel document tweaking
            $this->kernel->postParse($document);

            $dependencies = $document->getEnvironment()->getDependencies();

            if ($dependencies !== []) {
                $this->display(' -> Scanning dependencies of ' . $file . '...');
                // Scan the dependencies for this document
                foreach ($dependencies as $dependency) {
                    $this->scan($dependency);
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
}
