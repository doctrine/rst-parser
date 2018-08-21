<?php

declare(strict_types=1);

namespace Gregwar\RST;

use Gregwar\RST\Nodes\Node;
use Gregwar\RST\Nodes\RawNode;
use Gregwar\RST\Nodes\TitleNode;
use Gregwar\RST\Nodes\TocNode;
use function array_unshift;
use function count;
use function is_string;

abstract class Document extends Node
{
    /** @var Environment */
    protected $environment;

    /** @var Node[] */
    protected $headerNodes = [];

    /** @var Node[] */
    protected $nodes = [];

    public function __construct(Environment $environment)
    {
        parent::__construct();

        $this->environment = $environment;
    }

    public function getEnvironment() : Environment
    {
        return $this->environment;
    }

    public function renderDocument() : string
    {
        return $this->render();
    }

    /**
     * @return Node[]
     */
    public function getNodes(?callable $function = null) : array
    {
        $nodes = [];

        if ($function === null) {
            return $this->nodes;
        }

        foreach ($this->nodes as $node) {
            if (! $function($node)) {
                continue;
            }

            $nodes[] = $node;
        }

        return $nodes;
    }

    public function getTitle() : ?string
    {
        foreach ($this->nodes as $node) {
            if ($node instanceof TitleNode && $node->getLevel() === 1) {
                return $node->getValue() . '';
            }
        }

        return null;
    }

    /**
     * @return string[]
     */
    public function getTocs() : array
    {
        $tocs = [];

        $nodes = $this->getNodes(function ($node) {
            return $node instanceof TocNode;
        });

        /** @var TocNode $toc */
        foreach ($nodes as $toc) {
            $files = $toc->getFiles();

            foreach ($files as &$file) {
                $file = $this->environment->canonicalUrl($file);
            }

            $tocs[] = $files;
        }

        return $tocs;
    }

    /**
     * @return string[][]
     */
    public function getTitles() : array
    {
        $titles = [];
        $levels = [&$titles];

        foreach ($this->nodes as $node) {
            if (! ($node instanceof TitleNode)) {
                continue;
            }

            $level       = $node->getLevel();
            $text        = (string) $node->getValue();
            $redirection = $node->getTarget();
            $value       = $redirection !== '' ? [$text, $redirection] : $text;

            if (! isset($levels[$level-1])) {
                continue;
            }

            $parent         = &$levels[$level-1];
            $element        = [$value, []];
            $parent[]       = $element;
            $levels[$level] = &$parent[count($parent)-1][1];
        }

        return $titles;
    }

    /**
     * @param string|Node $node
     */
    public function addNode($node) : void
    {
        if (is_string($node)) {
            $node = new RawNode($node);
        }

        if (! $node instanceof Node) {
            $this->getEnvironment()->getErrorManager()->error('addNode($node): $node should be a string or a Node');
        }

        $this->nodes[] = $node;
    }

    public function prependNode(Node $node) : void
    {
        array_unshift($this->nodes, $node);
    }

    public function addHeaderNode(Node $node) : void
    {
        $this->headerNodes[] = $node;
    }

    public function __toString() : string
    {
        return $this->render();
    }
}
