<?php

declare(strict_types=1);

namespace Doctrine\RST;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventManager;
use Doctrine\RST\Formats\Format;
use Doctrine\RST\Formats\InternalFormat;
use Doctrine\RST\HTML\HTMLFormat;
use Doctrine\RST\LaTeX\LaTeXFormat;
use Doctrine\RST\NodeFactory\DefaultNodeFactory;
use Doctrine\RST\NodeFactory\NodeFactory;
use Doctrine\RST\NodeFactory\NodeInstantiator;
use Doctrine\RST\Nodes\NodeTypes;
use Doctrine\RST\Renderers\NodeRendererFactory;
use Doctrine\RST\Templates\TemplateRenderer;
use Doctrine\RST\Templates\TwigTemplateRenderer;
use RuntimeException;
use function sprintf;
use function sys_get_temp_dir;

class Configuration
{
    public const THEME_DEFAULT = 'default';

    /** @var string */
    private $cacheDir;

    /** @var string[] */
    private $customTemplateDirs = [];

    /** @var string */
    private $theme = self::THEME_DEFAULT;

    /** @var string */
    private $baseUrl = '';

    /** @var callable|null */
    private $baseUrlEnabledCallable;

    /** @var bool */
    private $abortOnError = true;

    /** @var bool */
    private $ignoreInvalidReferences = false;

    /** @var string */
    private $fileExtension = Format::HTML;

    /** @var TemplateRenderer */
    private $templateRenderer;

    /** @var Format[] */
    private $formats;

    /** @var NodeFactory|null */
    private $nodeFactory;

    /** @var EventManager */
    private $eventManager;

    public function __construct()
    {
        $this->cacheDir = sys_get_temp_dir() . '/doctrine-rst-parser';

        $this->templateRenderer = new TwigTemplateRenderer($this);

        $this->eventManager = new EventManager();

        $this->formats = [
            Format::HTML => new InternalFormat(new HTMLFormat($this->templateRenderer)),
            Format::LATEX => new InternalFormat(new LaTeXFormat($this->templateRenderer)),
        ];
    }

    public function getCacheDir() : string
    {
        return $this->cacheDir;
    }

    public function setCacheDir(string $cacheDir) : void
    {
        $this->cacheDir = $cacheDir;
    }

    public function getTemplateRenderer() : TemplateRenderer
    {
        return $this->templateRenderer;
    }

    public function setTemplateRenderer(TemplateRenderer $templateRenderer) : void
    {
        $this->templateRenderer = $templateRenderer;
    }

    /**
     * @return string[]
     */
    public function getCustomTemplateDirs() : array
    {
        return $this->customTemplateDirs;
    }

    /**
     * @param string[] $customTemplateDirs
     */
    public function setCustomTemplateDirs(array $customTemplateDirs) : void
    {
        $this->customTemplateDirs = $customTemplateDirs;
    }

    public function addCustomTemplateDir(string $customTemplateDir) : void
    {
        $this->customTemplateDirs[] = $customTemplateDir;
    }

    public function getTheme() : string
    {
        return $this->theme;
    }

    public function setTheme(string $theme) : void
    {
        $this->theme = $theme;
    }

    public function getBaseUrl() : string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl) : self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    public function setBaseUrlEnabledCallable(?callable $baseUrlEnabledCallable) : void
    {
        $this->baseUrlEnabledCallable = $baseUrlEnabledCallable;
    }

    public function getBaseUrlEnabledCallable() : ?callable
    {
        return $this->baseUrlEnabledCallable;
    }

    public function isBaseUrlEnabled(string $path) : bool
    {
        if ($this->baseUrl === '') {
            return false;
        }

        if ($this->baseUrlEnabledCallable !== null) {
            /** @var callable $baseUrlEnabledCallable */
            $baseUrlEnabledCallable = $this->baseUrlEnabledCallable;

            return $baseUrlEnabledCallable($path);
        }

        return true;
    }

    public function isAbortOnError() : bool
    {
        return $this->abortOnError;
    }

    public function abortOnError(bool $abortOnError) : void
    {
        $this->abortOnError = $abortOnError;
    }

    public function getIgnoreInvalidReferences() : bool
    {
        return $this->ignoreInvalidReferences;
    }

    public function setIgnoreInvalidReferences(bool $ignoreInvalidReferences) : void
    {
        $this->ignoreInvalidReferences = $ignoreInvalidReferences;
    }

    public function getFileExtension() : string
    {
        return $this->fileExtension;
    }

    public function setFileExtension(string $fileExtension) : void
    {
        $this->fileExtension = $fileExtension;
    }

    public function getNodeFactory() : NodeFactory
    {
        if ($this->nodeFactory !== null) {
            return $this->nodeFactory;
        }

        return new DefaultNodeFactory(
            $this->eventManager,
            $this->createNodeInstantiator(NodeTypes::DOCUMENT, Nodes\DocumentNode::class),
            $this->createNodeInstantiator(NodeTypes::SPAN, Nodes\SpanNode::class),
            $this->createNodeInstantiator(NodeTypes::TOC, Nodes\TocNode::class),
            $this->createNodeInstantiator(NodeTypes::TITLE, Nodes\TitleNode::class),
            $this->createNodeInstantiator(NodeTypes::SEPARATOR, Nodes\SeparatorNode::class),
            $this->createNodeInstantiator(NodeTypes::CODE, Nodes\CodeNode::class),
            $this->createNodeInstantiator(NodeTypes::QUOTE, Nodes\QuoteNode::class),
            $this->createNodeInstantiator(NodeTypes::PARAGRAPH, Nodes\ParagraphNode::class),
            $this->createNodeInstantiator(NodeTypes::ANCHOR, Nodes\AnchorNode::class),
            $this->createNodeInstantiator(NodeTypes::LIST, Nodes\ListNode::class),
            $this->createNodeInstantiator(NodeTypes::TABLE, Nodes\TableNode::class),
            $this->createNodeInstantiator(NodeTypes::DEFINITION_LIST, Nodes\DefinitionListNode::class),
            $this->createNodeInstantiator(NodeTypes::WRAPPER, Nodes\WrapperNode::class),
            $this->createNodeInstantiator(NodeTypes::FIGURE, Nodes\FigureNode::class),
            $this->createNodeInstantiator(NodeTypes::IMAGE, Nodes\ImageNode::class),
            $this->createNodeInstantiator(NodeTypes::META, Nodes\MetaNode::class),
            $this->createNodeInstantiator(NodeTypes::RAW, Nodes\RawNode::class),
            $this->createNodeInstantiator(NodeTypes::DUMMY, Nodes\DummyNode::class),
            $this->createNodeInstantiator(NodeTypes::MAIN, Nodes\MainNode::class),
            $this->createNodeInstantiator(NodeTypes::BLOCK, Nodes\BlockNode::class),
            $this->createNodeInstantiator(NodeTypes::CALLABLE, Nodes\CallableNode::class),
            $this->createNodeInstantiator(NodeTypes::SECTION_BEGIN, Nodes\SectionBeginNode::class),
            $this->createNodeInstantiator(NodeTypes::SECTION_END, Nodes\SectionEndNode::class)
        );
    }

    public function setNodeFactory(NodeFactory $nodeFactory) : void
    {
        $this->nodeFactory = $nodeFactory;
    }

    public function setEventManager(EventManager $eventManager) : void
    {
        $this->eventManager = $eventManager;
    }

    public function getEventManager() : EventManager
    {
        return $this->eventManager;
    }

    public function dispatchEvent(string $eventName, ?EventArgs $eventArgs = null) : void
    {
        $this->eventManager->dispatchEvent($eventName, $eventArgs);
    }

    public function addFormat(Format $format) : void
    {
        $this->formats[$format->getFileExtension()] = $format;
    }

    public function getFormat() : Format
    {
        if (! isset($this->formats[$this->fileExtension])) {
            throw new RuntimeException(
                sprintf('Format %s does not exist.', $this->fileExtension)
            );
        }

        return $this->formats[$this->fileExtension];
    }

    private function createNodeInstantiator(string $type, string $nodeClassName) : NodeInstantiator
    {
        return new NodeInstantiator(
            $type,
            $nodeClassName,
            $this->getNodeRendererFactory($nodeClassName),
            $this->eventManager
        );
    }

    private function getNodeRendererFactory(string $nodeClassName) : ?NodeRendererFactory
    {
        return $this->getFormat()->getNodeRendererFactories()[$nodeClassName] ?? null;
    }
}
