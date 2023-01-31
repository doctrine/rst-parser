<?php

declare(strict_types=1);

namespace Doctrine\RST\Directives;

use Doctrine\RST\References\Doc;
use Doctrine\RST\References\Reference;
use Doctrine\RST\TextRoles\CodeRole;

class BasicDirectiveFactory implements DirectiveFactory
{
    /** @return Directive[] */
    public function getDirectives(): array
    {
        return [
            new Admonition('admonition', ''),
            new Admonition('attention', 'Attention'),
            new Admonition('caution', 'Caution'),
            new Admonition('danger', 'Danger'),
            new Admonition('error', 'Error'),
            new Admonition('hint', 'Hint'),
            new Admonition('important', 'Important'),
            new Admonition('note', 'Note'),
            new Admonition('notice', 'Notice'),
            new Admonition('seealso', 'See also'),
            new Admonition('sidebar', 'sidebar'),
            new Admonition('tip', 'Tip'),
            new Admonition('warning', 'Warning'),
            new Ignored('todo'),
            new Ignored('index'),
            new Ignored('role'),
            new Ignored('highlight'),
            new Ignored('default-role'),
            new Dummy(),
            new CodeBlock(),
            new Contents(),
            new Literalinclude(),
            new Raw(),
            new Replace(),
            new Toctree(),
        ];
    }

    /** @return CodeRole[] */
    public function getTextRoles(): array
    {
        return [
            new CodeRole(),
        ];
    }

    /** @return Reference[] */
    public function getReferences(): array
    {
        return [
            new Doc(),
            new Doc('ref', true),
        ];
    }
}
