<?php

declare(strict_types=1);

namespace Doctrine\RST\TextRoles;

class CodeRole extends TextRole
{
    public function getName(): string
    {
        return 'code';
    }

    public function process(string $text): string
    {
        return '<code>' . $text . '</code>';
    }
}
