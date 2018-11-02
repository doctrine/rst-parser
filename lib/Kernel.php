<?php

declare(strict_types=1);

namespace Doctrine\RST;

use Doctrine\RST\References\Doc;
use function array_merge;

abstract class Kernel
{
    /** @var NodeFactory */
    protected $nodeFactory;

    /** @var Directive[] */
    protected $directives;

    /** @var Reference[] */
    protected $references;

    /**
     * @param Directive[] $directives
     * @param Reference[] $references
     */
    public function __construct(
        ?NodeFactory $nodeFactory = null,
        array $directives = [],
        array $references = []
    ) {
        $this->nodeFactory = $nodeFactory ?? $this->createNodeFactory();

        $this->directives = array_merge([
            new Directives\Dummy(),
            new Directives\CodeBlock(),
            new Directives\Raw(),
            new Directives\Replace(),
            new Directives\Toctree(),
            new Directives\Document(),
        ], $this->createDirectives(), $directives);

        $this->references = array_merge([
            new References\Doc(),
            new References\Doc('ref'),
        ], $this->createReferences(), $references);
    }

    abstract public function createEnvironment(?Configuration $configuration = null) : Environment;

    public function getNodeFactory() : NodeFactory
    {
        return $this->nodeFactory;
    }

    /**
     * @return Directive[]
     */
    public function getDirectives() : array
    {
        return $this->directives;
    }

    /**
     * @return Reference[]
     */
    public function getReferences() : array
    {
        return $this->references;
    }

    public function postParse(Document $document) : void
    {
    }

    public function initBuilder(Builder $builder) : void
    {
    }

    public function getFileExtension() : string
    {
        return 'txt';
    }

    /**
     * @return Doc[]
     */
    protected function createReferences() : array
    {
        return [];
    }

    abstract protected function createNodeFactory() : NodeFactory;

    /**
     * @return Directive[]
     */
    abstract protected function createDirectives() : array;
}
