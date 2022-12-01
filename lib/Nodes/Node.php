<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventManager;
use Doctrine\RST\Environment;
use Doctrine\RST\Event\PostNodeRenderEvent;
use Doctrine\RST\Event\PreNodeRenderEvent;
use Doctrine\RST\Renderers\DefaultNodeRenderer;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Renderers\NodeRendererFactory;
use Doctrine\RST\Renderers\RenderedNode;

use function implode;
use function ltrim;
use function strlen;
use function substr;
use function trim;

abstract class Node
{
    /** @var NodeRendererFactory|null */
    private $nodeRendererFactory;

    /** @var EventManager|null */
    private $eventManager;

    /** @var Environment|null */
    protected $environment;

    /** @var Node|string|null */
    protected $value;

    /** @var string[] */
    private $classes = [];

    /** @param Node|string|null $value */
    public function __construct($value = null)
    {
        $this->value = $value;
    }

    public function setNodeRendererFactory(NodeRendererFactory $nodeRendererFactory): void
    {
        $this->nodeRendererFactory = $nodeRendererFactory;
    }

    public function setEventManager(EventManager $eventManager): void
    {
        $this->eventManager = $eventManager;
    }

    public function setEnvironment(Environment $environment): void
    {
        $this->environment = $environment;
    }

    public function getEnvironment(): ?Environment
    {
        return $this->environment;
    }

    public function render(): string
    {
        $this->dispatchEvent(
            PreNodeRenderEvent::PRE_NODE_RENDER,
            new PreNodeRenderEvent($this)
        );

        $renderedNode = new RenderedNode($this, $this->doRender());

        $this->dispatchEvent(
            PostNodeRenderEvent::POST_NODE_RENDER,
            new PostNodeRenderEvent($renderedNode)
        );

        return $renderedNode->getRendered();
    }

    /** @return Node|string|null */
    public function getValue()
    {
        return $this->value;
    }

    /** @param Node|string|null $value */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /** @return string[] */
    public function getClasses(): array
    {
        return $this->classes;
    }

    public function getClassesString(): string
    {
        return implode(' ', $this->classes);
    }

    /** @param string[] $classes */
    public function setClasses(array $classes): void
    {
        $this->classes = $classes;
    }

    public function getValueString(): string
    {
        if ($this->value === null) {
            return '';
        }

        if ($this->value instanceof Node) {
            return $this->value->getValueString();
        }

        return $this->value;
    }

    /** @param string[] $lines */
    protected function normalizeLines(array $lines): string
    {
        if ($lines !== []) {
            $indentLevel = null;

            // find the indentation by locating the line with the fewest preceding whitespace
            foreach ($lines as $line) {
                // skip empty lines
                if (trim($line) === '') {
                    continue;
                }

                $startingWhitespace = strlen($line) - strlen(ltrim($line));
                if ($indentLevel !== null && $startingWhitespace > $indentLevel) {
                    continue;
                }

                $indentLevel = $startingWhitespace;
            }

            foreach ($lines as &$line) {
                $line = substr($line, $indentLevel);
            }
        }

        return implode("\n", $lines);
    }

    protected function doRender(): string
    {
        return $this->getRenderer()->render();
    }

    protected function getRenderer(): NodeRenderer
    {
        $renderer = $this->createRenderer();

        if ($renderer !== null) {
            return $renderer;
        }

        return $this->createDefaultRenderer();
    }

    private function createRenderer(): ?NodeRenderer
    {
        if ($this->nodeRendererFactory !== null) {
            return $this->nodeRendererFactory->create($this);
        }

        return null;
    }

    private function createDefaultRenderer(): NodeRenderer
    {
        return new DefaultNodeRenderer($this);
    }

    public function dispatchEvent(string $eventName, ?EventArgs $eventArgs = null): void
    {
        if ($this->eventManager === null) {
            return;
        }

        $this->eventManager->dispatchEvent($eventName, $eventArgs);
    }
}
