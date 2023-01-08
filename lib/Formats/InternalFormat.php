<?php

declare(strict_types=1);

namespace Doctrine\RST\Formats;

use Doctrine\RST\Directives\Directive;
use Doctrine\RST\Renderers\NodeRendererFactory;
use Doctrine\RST\TextRoles\TextRole;

final class InternalFormat implements Format
{
    /** @var Format */
    private $format;

    /** @var Directive[]|null */
    private $directives;

    /** @var NodeRendererFactory[]|null */
    private $nodeRendererFactories;

    /** @var TextRole[]|null */
    private ?array $textRoles = null;

    public function __construct(Format $format)
    {
        $this->format = $format;
    }

    public function getFileExtension(): string
    {
        return $this->format->getFileExtension();
    }

    /** @return Directive[] */
    public function getDirectives(): array
    {
        if ($this->directives === null) {
            $this->directives = $this->format->getDirectives();
        }

        return $this->directives;
    }

    /** @return TextRole[] */
    public function getTextRoles(): array
    {
        if ($this->textRoles === null) {
            $this->textRoles = $this->format->getTextRoles();
        }

        return $this->textRoles;
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
