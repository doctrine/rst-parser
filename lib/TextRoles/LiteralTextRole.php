<?php

declare(strict_types=1);

namespace Doctrine\RST\TextRoles;

use Doctrine\RST\Span\SpanToken;

class LiteralTextRole extends WrapperTextRole
{
    public function __construct()
    {
        parent::__construct(SpanToken::TYPE_LITERAL);
    }
}
