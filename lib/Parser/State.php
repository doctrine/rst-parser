<?php

declare(strict_types=1);

namespace Doctrine\RST\Parser;

class State
{
    public const BEGIN     = 0;
    public const NORMAL    = 1;
    public const DIRECTIVE = 2;
    public const BLOCK     = 3;
    public const TITLE     = 4;
    public const LIST      = 5;
    public const SEPARATOR = 6;
    public const CODE      = 7;
    public const TABLE     = 8;
    public const COMMENT   = 9;
}
