<?php

declare(strict_types=1);

namespace Doctrine\RST\TextRoles;

use Doctrine\RST\Span\SpanProcessor;
use Doctrine\RST\Span\SpanToken;

class LiteralTextRole extends SpecialTextRole
{
    public function __construct()
    {
        parent::__construct(SpanToken::TYPE_LITERAL);
    }

    public function getTokens(SpanProcessor $spanProcessor, string $span): string
    {
        return $this->replaceTokens($spanProcessor, $span, '/``(.+)``(?!`)/mUsi');
    }
}
