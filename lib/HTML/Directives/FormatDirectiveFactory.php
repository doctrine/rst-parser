<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Directives;

use Doctrine\RST\Directives\Data;
use Doctrine\RST\Directives\Directive;
use Doctrine\RST\Directives\DirectiveFactory;
use Doctrine\RST\Directives\Wrapper;
use Doctrine\RST\HTML\TextRoles\DocTextRole;
use Doctrine\RST\HTML\TextRoles\LinkTextRole;
use Doctrine\RST\TextRoles\DefinitionTextRole;
use Doctrine\RST\TextRoles\TextRole;
use Doctrine\RST\TextRoles\WrapperTextRole;

class FormatDirectiveFactory implements DirectiveFactory
{
    /** @return Directive[] */
    public function getDirectives(): array
    {
        return [
            new Wrapper('pull-quote'),
            new Wrapper('container'),
            new Wrapper('versionadded'),
            new Wrapper('deprecated'),
            new Wrapper('versionchanged'),
            new Data('rubric'),
            new Data('youtube'),
            new Image(),
            new Figure(),
            new Meta(),
            new Stylesheet(),
            new Title(),
            new Url(),
            new Div(),
            new Wrap('note'),
            new ClassDirective(),
        ];
    }

    /** @return TextRole[] */
    public function getTextRoles(): array
    {
        return [
            new LinkTextRole(),
            new DocTextRole(),
            new DocTextRole('ref', true),
            new WrapperTextRole('aspect'),
            new WrapperTextRole('command'),
            new WrapperTextRole('dfn'),
            new WrapperTextRole('file'),
            new WrapperTextRole('guilabel'),
            new WrapperTextRole('kbd'),
            new WrapperTextRole('mailheader'),
            new WrapperTextRole('math'),
            new WrapperTextRole('subscript', null, ['sub']),
            new WrapperTextRole('superscript', null, ['sup']),
            new WrapperTextRole('title-reference', null, ['t', 'title']),
            new DefinitionTextRole('abbreviation', null, ['abbr']),
        ];
    }
}
