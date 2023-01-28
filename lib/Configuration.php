<?php

declare(strict_types=1);

namespace Doctrine\RST;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventManager;
use Doctrine\RST\Directives\BasicDirectiveFactory;
use Doctrine\RST\Directives\Directive;
use Doctrine\RST\Directives\DirectiveFactory;
use Doctrine\RST\ErrorManager\DefaultErrorManagerFactory;
use Doctrine\RST\ErrorManager\ErrorManagerFactory;
use Doctrine\RST\Formats\Format;
use Doctrine\RST\Formats\InternalFormat;
use Doctrine\RST\HTML\HTMLFormat;
use Doctrine\RST\LaTeX\LaTeXFormat;
use Doctrine\RST\NodeFactory\DefaultNodeFactory;
use Doctrine\RST\NodeFactory\NodeFactory;
use Doctrine\RST\NodeFactory\NodeInstantiator;
use Doctrine\RST\Nodes\NodeTypes;
use Doctrine\RST\References\Reference;
use Doctrine\RST\Renderers\NodeRendererFactory;
use Doctrine\RST\Templates\TemplateEngineAdapter;
use Doctrine\RST\Templates\TemplateRenderer;
use Doctrine\RST\Templates\TwigAdapter;
use Doctrine\RST\Templates\TwigTemplateRenderer;
use Doctrine\RST\TextRoles\TextRole;
use RuntimeException;
use Twig\Environment as TwigEnvironment;

use function array_merge;
use function sprintf;
use function sys_get_temp_dir;

class Configuration
{
    public const THEME_DEFAULT         = 'default';
    public const OUTPUT_FORMAT_CONSOLE = 'console';
    public const OUTPUT_FORMAT_GITHUB  = 'github';

    /** @var string */
    private $cacheDir;

    /** @var string[] */
    private $customTemplateDirs = [];

    /** @var string */
    private $theme = self::THEME_DEFAULT;

    private string $indexName = 'index';

    /** @var string */
    private $baseUrl = '';

    /** @var callable|null */
    private $baseUrlEnabledCallable;

    /** @var bool */
    private $abortOnError = true;

    /** @var bool */
    private $silentOnError = false;

    /** @var bool */
    private $warningsAsError = false;

    /** @psalm-var self::OUTPUT_FORMAT_* */
    private $outputFormat = self::OUTPUT_FORMAT_CONSOLE;

    /** @var bool */
    private $ignoreInvalidReferences = false;

    /** @var int */
    private $initialHeaderLevel = 1;

    /** @var bool */
    private $useCachedMetas = true;

    /** @var string */
    private $fileExtension = Format::HTML;

    /** @var string */
    private $sourceFileExtension = 'rst';

    /** @var TemplateRenderer */
    private $templateRenderer;

    /** @var Format[] */
    private $formats;

    /** @var NodeFactory|null */
    private $nodeFactory;

    /** @var EventManager */
    private $eventManager;

    /** @var TemplateEngineAdapter */
    private $templateEngineAdapter;

    private ErrorManagerFactory $errorManagerFactory;

    /** @var DirectiveFactory[] */
    private array $directiveFactories = [];

    public function __construct()
    {
        $this->cacheDir = sys_get_temp_dir() . '/doctrine-rst-parser';

        $this->eventManager = new EventManager();

        $this->templateEngineAdapter = new TwigAdapter($this);
        $this->templateRenderer      = new TwigTemplateRenderer($this);
        $this->errorManagerFactory   = new DefaultErrorManagerFactory($this);

        $this->formats              = [
            Format::HTML => new InternalFormat(new HTMLFormat($this->templateRenderer)),
            Format::LATEX => new InternalFormat(new LaTeXFormat($this->templateRenderer)),
        ];
        $this->directiveFactories[] = new BasicDirectiveFactory();
    }

    public function getCacheDir(): string
    {
        return $this->cacheDir;
    }

    public function setCacheDir(string $cacheDir): void
    {
        $this->cacheDir = $cacheDir;
    }

    public function getTemplateRenderer(): TemplateRenderer
    {
        return $this->templateRenderer;
    }

    public function setTemplateRenderer(TemplateRenderer $templateRenderer): void
    {
        $this->templateRenderer = $templateRenderer;
    }

    /** @return mixed|TwigEnvironment */
    public function getTemplateEngine()
    {
        return $this->templateEngineAdapter->getTemplateEngine();
    }

    /** @return string[] */
    public function getCustomTemplateDirs(): array
    {
        return $this->customTemplateDirs;
    }

    /** @param string[] $customTemplateDirs */
    public function setCustomTemplateDirs(array $customTemplateDirs): void
    {
        $this->customTemplateDirs = $customTemplateDirs;
    }

    public function addCustomTemplateDir(string $customTemplateDir): void
    {
        $this->customTemplateDirs[] = $customTemplateDir;
    }

    public function getTheme(): string
    {
        return $this->theme;
    }

    public function setTheme(string $theme): void
    {
        $this->theme = $theme;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    public function setBaseUrlEnabledCallable(?callable $baseUrlEnabledCallable): void
    {
        $this->baseUrlEnabledCallable = $baseUrlEnabledCallable;
    }

    public function getBaseUrlEnabledCallable(): ?callable
    {
        return $this->baseUrlEnabledCallable;
    }

    public function isBaseUrlEnabled(string $path): bool
    {
        if ($this->baseUrl === '') {
            return false;
        }

        if ($this->baseUrlEnabledCallable !== null) {
            $baseUrlEnabledCallable = $this->baseUrlEnabledCallable;

            return $baseUrlEnabledCallable($path);
        }

        return true;
    }

    public function isAbortOnError(): bool
    {
        return $this->abortOnError;
    }

    public function abortOnError(bool $abortOnError): void
    {
        $this->abortOnError = $abortOnError;
    }

    public function silentOnError(bool $silentOnError = true): void
    {
        $this->silentOnError = $silentOnError;
    }

    public function isSilentOnError(): bool
    {
        return $this->silentOnError;
    }

    public function treatWarningsAsError(bool $warningsAsError): void
    {
        $this->warningsAsError = $warningsAsError;
    }

    public function isWarningsAsError(): bool
    {
        return $this->warningsAsError;
    }

    /** @psalm-return self::OUTPUT_FORMAT_* */
    final public function getOutputFormat(): string
    {
        return $this->outputFormat;
    }

    /** @psalm-param self::OUTPUT_FORMAT_* $outputFormat */
    final public function setOutputFormat(string $outputFormat): void
    {
        $this->outputFormat = $outputFormat;
    }

    public function getIgnoreInvalidReferences(): bool
    {
        return $this->ignoreInvalidReferences;
    }

    public function setIgnoreInvalidReferences(bool $ignoreInvalidReferences): void
    {
        $this->ignoreInvalidReferences = $ignoreInvalidReferences;
    }

    public function setInitialHeaderLevel(int $initialHeaderLevel): void
    {
        $this->initialHeaderLevel = $initialHeaderLevel;
    }

    public function getInitialHeaderLevel(): int
    {
        return $this->initialHeaderLevel;
    }

    public function setUseCachedMetas(bool $useCachedMetas): void
    {
        $this->useCachedMetas = $useCachedMetas;
    }

    public function getUseCachedMetas(): bool
    {
        return $this->useCachedMetas;
    }

    public function getFileExtension(): string
    {
        return $this->fileExtension;
    }

    public function setFileExtension(string $fileExtension): void
    {
        $this->fileExtension = $fileExtension;
    }

    /** @return DirectiveFactory[] */
    public function getDirectiveFactories(): array
    {
        return $this->directiveFactories;
    }

    /** @param DirectiveFactory[] $directiveFactories */
    public function setDirectiveFactories(array $directiveFactories): void
    {
        $this->directiveFactories = $directiveFactories;
    }

    public function addDirectiveFactory(DirectiveFactory $factory): void
    {
        $this->directiveFactories[] = $factory;
    }

    /** @return Directive[] */
    public function getDirectives(): array
    {
        $directives = [];
        foreach ($this->directiveFactories as $factory) {
            $directives = array_merge($directives, $factory->getDirectives());
        }

        return array_merge($directives, $this->getFormat()->getDirectiveFactory()->getDirectives());
    }

    /** @return TextRole[] */
    public function getTextRoles(): array
    {
        $textRoles = [];
        foreach ($this->directiveFactories as $factory) {
            $textRoles = array_merge($textRoles, $factory->getTextRoles());
        }

        return array_merge($textRoles, $this->getFormat()->getDirectiveFactory()->getTextRoles());
    }

    /** @return Reference[] */
    public function getReferences(): array
    {
        $reference = [];
        foreach ($this->directiveFactories as $factory) {
            $reference = array_merge($reference, $factory->getReferences());
        }

        return array_merge($reference, $this->getFormat()->getDirectiveFactory()->getReferences());
    }

    public function getNodeFactory(Environment $environment): NodeFactory
    {
        if ($this->nodeFactory !== null) {
            return $this->nodeFactory;
        }

        return new DefaultNodeFactory(
            $this->eventManager,
            $this->createNodeInstantiator($environment, NodeTypes::DOCUMENT, Nodes\DocumentNode::class),
            $this->createNodeInstantiator($environment, NodeTypes::SPAN, Nodes\SpanNode::class),
            $this->createNodeInstantiator($environment, NodeTypes::TOC, Nodes\TocNode::class),
            $this->createNodeInstantiator($environment, NodeTypes::CONTENTS, Nodes\ContentsNode::class),
            $this->createNodeInstantiator($environment, NodeTypes::TITLE, Nodes\TitleNode::class),
            $this->createNodeInstantiator($environment, NodeTypes::SEPARATOR, Nodes\SeparatorNode::class),
            $this->createNodeInstantiator($environment, NodeTypes::CODE, Nodes\CodeNode::class),
            $this->createNodeInstantiator($environment, NodeTypes::QUOTE, Nodes\QuoteNode::class),
            $this->createNodeInstantiator($environment, NodeTypes::PARAGRAPH, Nodes\ParagraphNode::class),
            $this->createNodeInstantiator($environment, NodeTypes::ANCHOR, Nodes\AnchorNode::class),
            $this->createNodeInstantiator($environment, NodeTypes::LIST, Nodes\ListNode::class),
            $this->createNodeInstantiator($environment, NodeTypes::TABLE, Nodes\TableNode::class),
            $this->createNodeInstantiator($environment, NodeTypes::DEFINITION_LIST, Nodes\DefinitionListNode::class),
            $this->createNodeInstantiator($environment, NodeTypes::WRAPPER, Nodes\WrapperNode::class),
            $this->createNodeInstantiator($environment, NodeTypes::FIGURE, Nodes\FigureNode::class),
            $this->createNodeInstantiator($environment, NodeTypes::IMAGE, Nodes\ImageNode::class),
            $this->createNodeInstantiator($environment, NodeTypes::META, Nodes\MetaNode::class),
            $this->createNodeInstantiator($environment, NodeTypes::RAW, Nodes\RawNode::class),
            $this->createNodeInstantiator($environment, NodeTypes::DUMMY, Nodes\DummyNode::class),
            $this->createNodeInstantiator($environment, NodeTypes::MAIN, Nodes\MainNode::class),
            $this->createNodeInstantiator($environment, NodeTypes::BLOCK, Nodes\BlockNode::class),
            $this->createNodeInstantiator($environment, NodeTypes::CALLABLE, Nodes\CallableNode::class),
            $this->createNodeInstantiator($environment, NodeTypes::SECTION_BEGIN, Nodes\SectionBeginNode::class),
            $this->createNodeInstantiator($environment, NodeTypes::SECTION_END, Nodes\SectionEndNode::class)
        );
    }

    public function setNodeFactory(NodeFactory $nodeFactory): void
    {
        $this->nodeFactory = $nodeFactory;
    }

    public function setEventManager(EventManager $eventManager): void
    {
        $this->eventManager = $eventManager;
    }

    public function getEventManager(): EventManager
    {
        return $this->eventManager;
    }

    public function dispatchEvent(string $eventName, ?EventArgs $eventArgs = null): void
    {
        $this->eventManager->dispatchEvent($eventName, $eventArgs);
    }

    public function addFormat(Format $format): void
    {
        $this->formats[$format->getFileExtension()] = $format;
    }

    public function getFormat(): Format
    {
        if (! isset($this->formats[$this->fileExtension])) {
            throw new RuntimeException(
                sprintf('Format %s does not exist.', $this->fileExtension)
            );
        }

        return $this->formats[$this->fileExtension];
    }

    public function getSourceFileExtension(): string
    {
        return $this->sourceFileExtension;
    }

    private function createNodeInstantiator(Environment $environment, string $type, string $nodeClassName): NodeInstantiator
    {
        return new NodeInstantiator(
            $type,
            $nodeClassName,
            $environment,
            $this->getNodeRendererFactory($nodeClassName),
            $this->eventManager
        );
    }

    private function getNodeRendererFactory(string $nodeClassName): ?NodeRendererFactory
    {
        return $this->getFormat()->getNodeRendererFactories()[$nodeClassName] ?? null;
    }

    public function getErrorManagerFactory(): ErrorManagerFactory
    {
        return $this->errorManagerFactory;
    }

    public function setErrorManagerFactory(ErrorManagerFactory $errorManagerFactory): void
    {
        $this->errorManagerFactory = $errorManagerFactory;
    }

    public function getErrorManager(): ErrorManager
    {
        return $this->errorManagerFactory->getErrorManager();
    }

    public function getIndexName(): string
    {
        return $this->indexName;
    }

    public function setIndexName(string $indexName): void
    {
        $this->indexName = $indexName;
    }

    /**
     * Returns the "master document" name (e.g. index).
     *
     * This is the first document whose toctree should be read
     * when building the table of contents. It is usually "index.rst",
     * which means index.rst is read first.
     */
    public function getIndexFileName(): string
    {
        return sprintf('%s.%s', $this->getIndexName(), $this->getSourceFileExtension());
    }
}
