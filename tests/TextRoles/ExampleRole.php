<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\TextRoles;

use Doctrine\RST\TextRoles\TextRole;

class ExampleRole extends TextRole
{
    public function getName(): string
    {
        return 'example';
    }

    public function process(string $text): string
    {
        return '<samp>' . $text . '</samp>';
    }
}
