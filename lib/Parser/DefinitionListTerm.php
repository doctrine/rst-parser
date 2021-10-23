<?php

declare(strict_types=1);

namespace Doctrine\RST\Parser;

use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\SpanNode;
use RuntimeException;

final class DefinitionListTerm
{
    /** @var SpanNode */
    private $term;

    /** @var SpanNode[] */
    private $classifiers = [];

    /** @var Node[] */
    private $definitions = [];

    /**
     * @param SpanNode[] $classifiers
     * @param Node[]     $definitions
     */
    public function __construct(SpanNode $term, array $classifiers, array $definitions)
    {
        $this->term        = $term;
        $this->classifiers = $classifiers;
        $this->definitions = $definitions;
    }

    public function getTerm(): SpanNode
    {
        return $this->term;
    }

    /**
     * @return SpanNode[]
     */
    public function getClassifiers(): array
    {
        return $this->classifiers;
    }

    /**
     * @return Node[]
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    public function getFirstDefinition(): Node
    {
        if (! isset($this->definitions[0])) {
            throw new RuntimeException('No definitions found.');
        }

        return $this->definitions[0];
    }
}
