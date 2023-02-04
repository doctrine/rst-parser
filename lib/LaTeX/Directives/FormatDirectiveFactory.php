<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Directives;

use Doctrine\RST\Directives\Directive;
use Doctrine\RST\Directives\DirectiveFactory;
use Doctrine\RST\LaTeX\TextRoles\DocTextRole;
use Doctrine\RST\LaTeX\TextRoles\LinkTextRole;
use Doctrine\RST\TextRoles\TextRole;

class FormatDirectiveFactory implements DirectiveFactory
{
    /** @return Directive[] */
    public function getDirectives(): array
    {
        return [
            new LaTeXMain(),
            new Image(),
            new Meta(),
            new Stylesheet(),
            new Title(),
            new Url(),
            new Wrap('note'),
        ];
    }

    /** @return TextRole[] */
    public function getTextRoles(): array
    {
        return [
            new LinkTextRole(),
            new DocTextRole(),
            new DocTextRole('ref', true),
        ];
    }
}
