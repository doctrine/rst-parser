<?php

declare(strict_types=1);

namespace Doctrine\RST\Directives;

use Doctrine\RST\References\Reference;
use Doctrine\RST\TextRoles\TextRole;

class CustomDirectiveFactory implements DirectiveFactory
{
    /** @var Directive[]  */
    private array $directives;
    /** @var TextRole[] */
    private array $textRoles;
    /** @var Reference[]  */
    private array $references;

    /**
     * @param Directive[] $directives
     * @param TextRole[]  $textRoles
     * @param Reference[] $references
     */
    public function __construct(array $directives = [], array $textRoles = [], array $references = [])
    {
        $this->directives = $directives;
        $this->textRoles  = $textRoles;
        $this->references = $references;
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

    /** @return Reference[] */
    public function getReferences(): array
    {
        return $this->references;
    }
}
