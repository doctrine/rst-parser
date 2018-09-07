<?php

declare(strict_types=1);

namespace Doctrine\RST;

use Doctrine\RST\References\Doc;
use function array_merge;

abstract class Kernel
{
    /** @var Directive[] */
    private $directives;

    /** @var Factory */
    private $factory;

    /**
     * @param Directive[] $directives
     */
    public function __construct(array $directives = [])
    {
        $this->directives = array_merge([
            new Directives\Dummy(),
            new Directives\CodeBlock(),
            new Directives\Raw(),
            new Directives\Replace(),
            new Directives\Toctree(),
            new Directives\Document(),
            new Directives\RedirectionTitle(),
        ], $directives);

        $this->factory = new Factory($this->getName());
    }

    abstract protected function getName() : string;

    public function getFactory() : Factory
    {
        return $this->factory;
    }

    /**
     * @return Directive[]
     */
    public function getDirectives() : array
    {
        return $this->directives;
    }

    /**
     * @return Doc[]
     */
    public function getReferences() : array
    {
        return [
            new References\Doc(),
            new References\Doc('ref'),
        ];
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
}
