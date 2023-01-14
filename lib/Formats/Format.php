<?php

declare(strict_types=1);

namespace Doctrine\RST\Formats;

use Doctrine\RST\Directives\Directive;
use Doctrine\RST\Renderers\NodeRendererFactory;
use Doctrine\RST\TextRoles\TextRole;

interface Format
{
    public const HTML  = 'html';
    public const LATEX = 'tex';

    public function getFileExtension(): string;

    /** @return Directive[] */
    public function getDirectives(): array;

    /** @return TextRole[] */
    public function getTextRoles(): array;

    /** @return NodeRendererFactory[] */
    public function getNodeRendererFactories(): array;
}
