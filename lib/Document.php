<?php

declare(strict_types=1);

namespace Doctrine\RST;

use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\RawNode;
use Doctrine\RST\Nodes\TitleNode;
use Doctrine\RST\Nodes\TocNode;
use function array_unshift;
use function count;
use function is_string;
use function sprintf;

abstract class Document extends Node
{
    /** @var Environment */
    protected $environment;

    /** @var Configuration */
    protected $configuration;

    /** @var ErrorManager */
    protected $errorManager;

    /** @var Node[] */
    protected $headerNodes = [];

    /** @var Node[] */
    protected $nodes = [];

    public function __construct(Environment $environment)
    {
        parent::__construct();

        $this->environment   = $environment;
        $this->configuration = $environment->getConfiguration();
        $this->errorManager  = $environment->getErrorManager();
    }

    public function getEnvironment() : Environment
    {
        return $this->environment;
    }

    public function renderDocument() : string
    {
        $renderedDocument = $this->doRenderDocument();

        $this->postRenderValidate();

        return $renderedDocument;
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
     * @return mixed[]
     */
    public function getTocs() : array
    {
        $tocs = [];

        $nodes = $this->getNodes(static function ($node) {
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

            if (! isset($levels[$level - 1])) {
                continue;
            }

            $parent         = &$levels[$level - 1];
            $element        = [$value, []];
            $parent[]       = $element;
            $levels[$level] = &$parent[count($parent) - 1][1];
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

    protected function doRenderDocument() : string
    {
        return $this->render();
    }

    private function postRenderValidate() : void
    {
        if ($this->configuration->getIgnoreInvalidReferences() !== false) {
            return;
        }

        $currentFileName = $this->environment->getCurrentFileName();

        foreach ($this->environment->getInvalidLinks() as $invalidLink) {
            $this->errorManager->error(sprintf(
                'Found invalid reference "%s"%s',
                $invalidLink->getName(),
                $currentFileName !== '' ? sprintf(' in file "%s"', $currentFileName) : ''
            ));
        }
    }
}
