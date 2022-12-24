<?php

declare(strict_types=1);

namespace Doctrine\RST;

use Doctrine\RST\Directives\Directive;
use Doctrine\RST\Nodes\DocumentNode;
use Doctrine\RST\References\Doc;
use Doctrine\RST\References\Reference;

use function array_merge;

class Kernel
{
    /** @var Configuration */
    private $configuration;

    /** @var Directive[] */
    private $directives;

    /** @var Reference[] */
    private $references;

    /**
     * @param Directive[] $directives
     * @param Reference[] $references
     */
    public function __construct(
        ?Configuration $configuration = null,
        array $directives = [],
        array $references = []
    ) {
        $this->configuration = $configuration ?? new Configuration();

        $this->directives = array_merge([
            new Directives\Dummy(),
            new Directives\CodeBlock(),
            new Directives\Contents(),
            new Directives\Raw(),
            new Directives\Replace(),
            new Directives\Tip(),
            new Directives\Toctree(),
        ], $this->configuration->getFormat()->getDirectives(), $directives);

        $this->references = array_merge([
            new References\Doc(),
            new References\Doc('ref', true),
        ], $this->createReferences(), $references);
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    /** @return Directive[] */
    public function getDirectives(): array
    {
        return $this->directives;
    }

    /** @return Reference[] */
    public function getReferences(): array
    {
        return $this->references;
    }

    public function postParse(DocumentNode $document): void
    {
    }

    public function initBuilder(Builder $builder): void
    {
    }

    /** @return Doc[] */
    protected function createReferences(): array
    {
        return [];
    }
}
