<?php

declare(strict_types=1);

namespace Doctrine\RST\Directives;

use Doctrine\RST\TextRoles\TextRole;

class CustomDirectiveFactory implements DirectiveFactory
{
    /** @var Directive[]  */
    private array $directives;
    /** @var TextRole[] */
    private array $textRoles;

    /**
     * @param Directive[] $directives
     * @param TextRole[]  $textRoles
     */
    public function __construct(array $directives = [], array $textRoles = [])
    {
        $this->directives = $directives;
        $this->textRoles  = $textRoles;
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
