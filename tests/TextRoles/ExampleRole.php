<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\TextRoles;

use Doctrine\RST\Environment;
use Doctrine\RST\Span\SpanToken;
use Doctrine\RST\TextRoles\BaseTextRole;

class ExampleRole extends BaseTextRole
{
    public function getName(): string
    {
        return 'example';
    }

    public function render(Environment $environment, SpanToken $spanToken): string
    {
        return '<samp>' . $spanToken->get('text') . '</samp>';
    }
}
