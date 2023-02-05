<?php

declare(strict_types=1);

namespace Doctrine\RST\Formats;

use Doctrine\RST\Directives\DirectiveFactory;
use Doctrine\RST\Renderers\NodeRendererFactory;

final class InternalFormat implements Format
{
    private Format $format;

    private ?DirectiveFactory $directiveFactory = null;

    /** @var NodeRendererFactory[]|null */
    private ?array $nodeRendererFactories = null;

    public function __construct(Format $format)
    {
        $this->format = $format;
    }

    public function getFileExtension(): string
    {
        return $this->format->getFileExtension();
    }

    public function getDirectiveFactory(): DirectiveFactory
    {
        if ($this->directiveFactory === null) {
            $this->directiveFactory = $this->format->getDirectiveFactory();
        }

        return $this->directiveFactory;
    }

    /** @return NodeRendererFactory[] */
    public function getNodeRendererFactories(): array
    {
        if ($this->nodeRendererFactories === null) {
            $this->nodeRendererFactories = $this->format->getNodeRendererFactories();
        }

        return $this->nodeRendererFactories;
    }
}
