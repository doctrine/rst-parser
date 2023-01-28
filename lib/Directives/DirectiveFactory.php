<?php

declare(strict_types=1);

namespace Doctrine\RST\Directives;

use Doctrine\RST\TextRoles\TextRole;

interface DirectiveFactory
{
    /** @return Directive[] */
    public function getDirectives(): array;

    /** @return TextRole[] */
    public function getTextRoles(): array;
}
