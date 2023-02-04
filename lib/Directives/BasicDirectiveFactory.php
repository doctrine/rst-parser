<?php

declare(strict_types=1);

namespace Doctrine\RST\Directives;

use Doctrine\RST\TextRoles\BrTextRole;
use Doctrine\RST\TextRoles\CodeRole;
use Doctrine\RST\TextRoles\Doc;
use Doctrine\RST\TextRoles\InterpretedTextRole;
use Doctrine\RST\TextRoles\LinkTextRole;
use Doctrine\RST\TextRoles\LiteralTextRole;
use Doctrine\RST\TextRoles\TextRole;

class BasicDirectiveFactory implements DirectiveFactory
{
    /** @var Directive[]  */
    private array $directives;
    /** @var TextRole[] */
    private array $textRoles;

    public function __construct()
    {
        $this->directives =  [
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
        $this->textRoles  = [
            new BrTextRole(),
            new LiteralTextRole(),
            new InterpretedTextRole(),
            new LinkTextRole(),
            new CodeRole(),
            new Doc(),
            new Doc('ref', true),
        ];
    }

    /** @return Directive[] */
    public function getDirectives(): array
    {
        return $this->directives;
    }

    /** @return TextRole[] */
    public function getTextRoles(): array
    {
        return $this->textRoles;
    }
}
