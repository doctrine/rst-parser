<?php

declare(strict_types=1);

namespace Doctrine\RST;

use Doctrine\RST\HTML\Kernel as HTMLKernel;
use Doctrine\RST\Parser\DocumentParser;
use InvalidArgumentException;
use RuntimeException;
use function file_get_contents;
use function sprintf;

class Parser
{
    /** @var Environment */
    private $environment;

    /** @var Kernel */
    private $kernel;

    /** @var Factory */
    private $factory;

    /** @var Directive[] */
    private $directives = [];

    /** @var bool */
    private $includeAllowed = true;

    /** @var string */
    private $includeRoot = '';

    /** @var string|null */
    private $filename = null;

    /** @var DocumentParser|null */
    private $documentParser;

    public function __construct(
        ?Environment $environment = null,
        ?Kernel $kernel = null,
        ?Configuration $configuration = null
    ) {
        if ($kernel === null) {
            $kernel = new HTMLKernel();
        }

        $this->kernel      = $kernel;
        $this->factory     = $this->kernel->getFactory();
        $this->environment = $environment ?: $this->factory->createEnvironment($configuration);

        $this->initDirectives();
        $this->initReferences();
    }

    public function getSubParser() : Parser
    {
        return new Parser($this->environment, $this->kernel);
    }

    public function initDirectives() : void
    {
        $directives = $this->kernel->getDirectives();

        foreach ($directives as $name => $directive) {
            $this->registerDirective($directive);
        }
    }

    public function initReferences() : void
    {
        $references = $this->kernel->getReferences();

        foreach ($references as $reference) {
            $this->environment->registerReference($reference);
        }
    }

    public function getEnvironment() : Environment
    {
        return $this->environment;
    }

    public function getKernel() : Kernel
    {
        return $this->kernel;
    }

    public function registerDirective(Directive $directive) : void
    {
        $this->directives[$directive->getName()] = $directive;
    }

    public function getDocument() : Document
    {
        if ($this->documentParser === null) {
            throw new RuntimeException('Nothing has been parsed yet.');
        }

        return $this->documentParser->getDocument();
    }

    public function getFilename() : string
    {
        return $this->filename ?: '(unknown)';
    }

    public function getIncludeAllowed() : bool
    {
        return $this->includeAllowed;
    }

    public function getIncludeRoot() : string
    {
        return $this->includeRoot;
    }

    public function setIncludePolicy(bool $includeAllowed, ?string $directory = null) : self
    {
        $this->includeAllowed = $includeAllowed;

        if ($directory !== null) {
            $this->includeRoot = $directory;
        }

        return $this;
    }

    /**
     * @param string|string[]|Span $span
     */
    public function createSpan($span) : Span
    {
        return $this->factory->createSpan($this, $span);
    }

    public function parse(string $contents) : Document
    {
        $this->getEnvironment()->reset();

        return $this->parseLocal($contents);
    }

    public function parseLocal(string $contents) : Document
    {
        $this->documentParser = new DocumentParser(
            $this,
            $this->environment,
            $this->factory,
            $this->directives,
            $this->includeAllowed,
            $this->includeRoot
        );

        return $this->documentParser->parse($contents);
    }

    public function parseFile(string $file) : Document
    {
        $this->filename = $file;

        $contents = file_get_contents($file);

        if ($contents === false) {
            throw new InvalidArgumentException(sprintf('Could not load file from path %s', $file));
        }

        return $this->parse($contents);
    }
}
