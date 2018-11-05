<?php

declare(strict_types=1);

namespace Doctrine\RST\Parser;

use Doctrine\RST\Span;
use RuntimeException;

class DefinitionListTerm
{
    /** @var Span */
    private $term;

    /** @var Span[] */
    private $classifiers = [];

    /** @var Span[] */
    private $definitions = [];

    /**
     * @param Span[] $classifiers
     * @param Span[] $definitions
     */
    public function __construct(Span $term, array $classifiers, array $definitions)
    {
        $this->term        = $term;
        $this->classifiers = $classifiers;
        $this->definitions = $definitions;
    }

    public function getTerm() : Span
    {
        return $this->term;
    }

    /**
     * @return Span[]
     */
    public function getClassifiers() : array
    {
        return $this->classifiers;
    }

    /**
     * @return Span[]
     */
    public function getDefinitions() : array
    {
        return $this->definitions;
    }

    public function getFirstDefinition() : Span
    {
        if (! isset($this->definitions[0])) {
            throw new RuntimeException('No definitions found.');
        }

        return $this->definitions[0];
    }
}
