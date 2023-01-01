<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Directives;

use Doctrine\RST\Directives\Data;
use Doctrine\RST\Directives\Directive;
use Doctrine\RST\Directives\DirectiveFactory;
use Doctrine\RST\Directives\Wrapper;
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
            new WrapperTextRole('aspect', '<em class="aspect">%s</em>'),
            new WrapperTextRole('command', '<strong class="command">%s</strong>'),
            new WrapperTextRole('dfn', '<em class="dfn">%s</em>'),
            new WrapperTextRole('file', '<span class="pre file">%s</span>'),
            new WrapperTextRole('guilabel', '<span class="guilabel">%s</span>'),
            new WrapperTextRole('kbd', '<kbd class="kbd docutils literal notranslate">%s</kbd>'),
            new WrapperTextRole('mailheader', '<em class="mailheader">%s</em>'),
            new WrapperTextRole('math', '<math>%s</math>'),
            new WrapperTextRole('emphasis', '<em>%s</em>'),
            new WrapperTextRole('literal', '<span class="pre">%s</span>'),
            new WrapperTextRole('strong', '<strong>%s</strong>'),
            new WrapperTextRole('subscript', '<sub>%s</sub>', ['sub']),
            new WrapperTextRole('superscript', '<sup>%s</sup>', ['sup']),
            new WrapperTextRole('title-reference', '<cite>%s</cite>', ['t', 'title']),
            new DefinitionTextRole('abbreviation', '<abbr title="%2$s">%1$s</abbr>', ['abbr']),
        ];
    }
}
