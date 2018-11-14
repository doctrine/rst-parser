<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

use Doctrine\RST\Renderers\DefaultNodeRenderer;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Renderers\NodeRendererFactory;
use function implode;
use function strlen;
use function substr;
use function trim;

abstract class Node
{
    /** @var NodeRendererFactory|null */
    private $nodeRendererFactory;

    /** @var Node|string|null */
    protected $value;

    /**
     * @param Node|string|null $value
     */
    public function __construct($value = null)
    {
        $this->value = $value;
    }

    public function setNodeRendererFactory(NodeRendererFactory $nodeRendererFactory) : void
    {
        $this->nodeRendererFactory = $nodeRendererFactory;
    }

    public function render() : string
    {
        return $this->doRender();
    }

    /**
     * @return Node|string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param Node|string|null $value
     */
    public function setValue($value) : void
    {
        $this->value = $value;
    }

    public function getValueString() : string
    {
        if ($this->value === null) {
            return '';
        }

        if ($this->value instanceof Node) {
            return $this->value->getValueString();
        }

        return $this->value;
    }

    /**
     * @param string[] $lines
     */
    protected function normalizeLines(array $lines) : string
    {
        if ($lines !== []) {
            $firstLine = $lines[0];

            $k = 0;

            for ($k = 0; $k < strlen($firstLine); $k++) {
                if (trim($firstLine[$k]) !== '') {
                    break;
                }
            }

            foreach ($lines as &$line) {
                $line = substr($line, $k);
            }
        }

        return implode("\n", $lines);
    }

    protected function doRender() : string
    {
        return $this->getRenderer()->render();
    }

    protected function getRenderer() : NodeRenderer
    {
        $renderer = $this->createRenderer();

        if ($renderer !== null) {
            return $renderer;
        }

        return $this->createDefaultRenderer();
    }

    private function createRenderer() : ?NodeRenderer
    {
        if ($this->nodeRendererFactory !== null) {
            return $this->nodeRendererFactory->create($this);
        }

        return null;
    }

    protected function createDefaultRenderer() : NodeRenderer
    {
        return new DefaultNodeRenderer($this);
    }
}
