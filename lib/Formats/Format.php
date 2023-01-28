<?php

declare(strict_types=1);

namespace Doctrine\RST\Formats;

use Doctrine\RST\Directives\DirectiveFactory;
use Doctrine\RST\Renderers\NodeRendererFactory;

interface Format
{
    public const HTML  = 'html';
    public const LATEX = 'tex';

    public function getFileExtension(): string;

    public function getDirectiveFactory(): DirectiveFactory;

    /** @return NodeRendererFactory[] */
    public function getNodeRendererFactories(): array;
}
