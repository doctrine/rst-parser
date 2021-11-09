<?php

declare(strict_types=1);

namespace Doctrine\RST;

use Doctrine\RST\Directives\Directive;
use Doctrine\RST\NodeFactory\NodeFactory;
use Doctrine\RST\Nodes\DocumentNode;
use Doctrine\RST\Nodes\SpanNode;
use Doctrine\RST\Parser\DocumentParser;
use InvalidArgumentException;
use RuntimeException;

use function file_exists;
use function file_get_contents;
use function fwrite;
use function getenv;
use function sprintf;

class Parser
{
    /** @var Configuration */
    private $configuration;

    /** @var Kernel */
    private $kernel;

    /** @var Environment */
    private $environment;

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
        ?Kernel $kernel = null,
        ?Environment $environment = null
    ) {
        if ($kernel === null) {
            $kernel = new Kernel();
        }

        $this->configuration = $kernel->getConfiguration();
        $this->kernel        = $kernel;
        $this->environment   = $environment ??  new Environment($this->configuration);

        $this->initDirectives();
        $this->initReferences();
    }

    public function getSubParser(): Parser
    {
        return new Parser($this->kernel, $this->environment);
    }

    public function getNodeFactory(): NodeFactory
    {
        return $this->configuration->getNodeFactory($this->environment);
    }

    /**
     * @param mixed[] $parameters
     */
    public function renderTemplate(string $template, array $parameters = []): string
    {
        return $this->configuration->getTemplateRenderer()->render($template, $parameters);
    }

    public function initDirectives(): void
    {
        $directives = $this->kernel->getDirectives();

        foreach ($directives as $directive) {
            $this->registerDirective($directive);
        }
    }

    public function initReferences(): void
    {
        $references = $this->kernel->getReferences();

        foreach ($references as $reference) {
            $this->environment->registerReference($reference);
        }
    }

    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    public function getKernel(): Kernel
    {
        return $this->kernel;
    }

    public function registerDirective(Directive $directive): void
    {
        $this->directives[$directive->getName()] = $directive;
    }

    public function getDocument(): DocumentNode
    {
        if ($this->documentParser === null) {
            throw new RuntimeException('Nothing has been parsed yet.');
        }

        return $this->documentParser->getDocument();
    }

    public function getFilename(): string
    {
        return $this->filename ?? '(unknown)';
    }

    public function getIncludeAllowed(): bool
    {
        return $this->includeAllowed;
    }

    public function getIncludeRoot(): string
    {
        return $this->includeRoot;
    }

    public function setIncludePolicy(bool $includeAllowed, ?string $directory = null): self
    {
        $this->includeAllowed = $includeAllowed;

        if ($directory !== null) {
            $this->includeRoot = $directory;
        }

        return $this;
    }

    /**
     * @param string|string[]|SpanNode $span
     */
    public function createSpanNode($span): SpanNode
    {
        return $this->getNodeFactory()->createSpanNode($this, $span);
    }

    /**
     * Parses the given contents as a new file.
     */
    public function parse(string $contents): DocumentNode
    {
        $this->getEnvironment()->reset();

        return $this->parseLocal($contents);
    }

    /**
     * Parses the given contents in a new document node.
     *
     * CAUTION: This modifies the state of the Parser, do not use this method
     * on the main parser (use `Parser::getSubParser()->parseLocal(...)` instead).
     *
     * Use this method to parse contents of an other node. Nodes created by
     * this new parser are not added to the main DocumentNode.
     */
    public function parseLocal(string $contents): DocumentNode
    {
        $this->documentParser = $this->createDocumentParser();

        return $this->documentParser->parse($contents);
    }

    public function parseFragment(string $contents): DocumentNode
    {
        return $this->createDocumentParser()->parse($contents);
    }

    public function parseFile(string $file): DocumentNode
    {
        if (getenv('SHELL_VERBOSITY') >= 2) {
            fwrite(fopen('php://stderr', 'wb'), sprintf("Parsing file: %s\n", $file));
        }

        if (! file_exists($file)) {
            throw new InvalidArgumentException(sprintf('File at path %s does not exist', $file));
        }

        $this->filename = $file;

        $contents = file_get_contents($file);

        if ($contents === false) {
            throw new InvalidArgumentException(sprintf('Could not load file from path %s', $file));
        }

        return $this->parse($contents);
    }

    private function createDocumentParser(): DocumentParser
    {
        return new DocumentParser(
            $this,
            $this->environment,
            $this->getNodeFactory(),
            $this->configuration->getEventManager(),
            $this->directives,
            $this->includeAllowed,
            $this->includeRoot
        );
    }
}
