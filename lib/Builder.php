<?php

declare(strict_types=1);

namespace Doctrine\RST;

use function array_shift;
use function basename;
use function dirname;
use function file_exists;
use function file_put_contents;
use function filectime;
use function is_dir;
use function mkdir;
use function shell_exec;
use function var_export;

class Builder
{
    public const NO_PARSE = 1;
    public const PARSE    = 2;

    /** @var string */
    protected $indexName = 'index';

    /** @var ErrorManager */
    protected $errorManager;

    /** @var bool */
    protected $verbose = true;

    /** @var string[][] */
    protected $toCopy = [];

    /** @var string[] */
    protected $toMkdir = [];

    /** @var string */
    protected $directory;

    /** @var string */
    protected $targetDirectory;

    /** @var Metas */
    protected $metas;

    /** @var int[] */
    protected $states = [];

    /** @var string[] */
    protected $parseQueue = [];

    /** @var Document[] */
    protected $documents = [];

    /** @var Kernel */
    protected $kernel;

    /** @var callable[] */
    protected $beforeHooks = [];

    /** @var callable[] */
    protected $hooks = [];

    /** @var bool */
    protected $relativeUrls = true;

    public function __construct(?Kernel $kernel = null)
    {
        $this->errorManager = new ErrorManager();

        $this->kernel = $kernel ?? new HTML\Kernel();

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
            mkdir($targetDirectory, 0755, true);
        }

        // Try to load metas, if it does not exists, create it
        $this->display('* Loading metas');

        $this->metas = new Metas($this->loadMetas());

        // Scan all the metas and the index
        $this->display('* Pre-scanning files');
        $this->scan($this->getIndexName());
        $this->scanMetas();

        // Parses all the documents
        $this->parseAll();

        // Renders all the documents
        $this->render();

        // Saving the meta
        $this->display('* Writing metas');
        $this->saveMetas();

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

        if ($entry === null || ! file_exists($rst) || $entry['ctime'] < filectime($rst)) {
            // File was never seen or changed and thus need to be parsed
            $this->addToParseQueue($file);
        } else {
            // Have a look to the file dependencies to knoww if you need to parse
            // it or not
            $depends = $entry['depends'];

            if (isset($entry['parent'])) {
                $depends[] = $entry['parent'];
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
        $meta = $this->metas->get($file);

        return $this->getTargetFile($meta['url']);
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
            list($source, $destination) = $copy;

            if ($source[0] !== '/') {
                $source = $this->getSourceFile($source);
            }

            $destination = $this->getTargetFile($destination);

            if (is_dir($source) && is_dir($destination)) {
                $destination = dirname($destination);
            }

            shell_exec('cp -R ' . $source . ' ' . $destination);
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

            mkdir($dir, 0755, true);
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

    public function setUseRelativeUrls(bool $relativeUrls) : void
    {
        $this->relativeUrls = $relativeUrls;
    }

    protected function display(string $text) : void
    {
        if (! $this->verbose) {
            return;
        }

        echo $text . "\n";
    }

    protected function render() : void
    {
        $this->display('* Rendering documents');

        foreach ($this->documents as $file => &$document) {
            $this->display(' -> Rendering ' . $file . '...');
            $target = $this->getTargetOf($file);

            $directory = dirname($target);

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            file_put_contents($target, $document->renderDocument());
        }
    }

    protected function addToParseQueue(string $file) : void
    {
        $this->states[$file] = self::PARSE;

        if (isset($this->documents[$file])) {
            return;
        }

        $this->parseQueue[$file] = $file;
    }

    protected function getFileToParse() : ?string
    {
        if ($this->parseQueue !== []) {
            return array_shift($this->parseQueue);
        }

        return null;
    }

    protected function parseAll() : void
    {
        $this->display('* Parsing files');

        while ($file = $this->getFileToParse()) {
            $this->display(' -> Parsing ' . $file . '...');
            // Process the file
            $rst = $this->getRST($file);

            if (! file_exists($rst)) {
                continue;
            }

            $parser = new Parser(null, $this->kernel);

            $environment = $parser->getEnvironment();
            $environment->setMetas($this->metas);
            $environment->setCurrentFileName($file);
            $environment->setCurrentDirectory($this->directory);
            $environment->setTargetDirectory($this->targetDirectory);
            $environment->setErrorManager($this->errorManager);
            $environment->setUseRelativeUrls($this->relativeUrls);

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
                $document->getTitle(),
                $document->getTitles(),
                $document->getTocs(),
                (int) filectime($rst),
                $dependencies
            );
        }
    }

    protected function getMetaFile() : string
    {
        return $this->getTargetFile('meta.php');
    }

    /**
     * @return mixed[]|null
     */
    protected function loadMetas() : ?array
    {
        $metaFile = $this->getMetaFile();

        if (file_exists($metaFile)) {
            return @include $metaFile;
        }

        return null;
    }

    protected function saveMetas() : void
    {
        $metas = '<?php return ' . var_export($this->metas->getAll(), true) . ';';

        file_put_contents($this->getMetaFile(), $metas);
    }
}
