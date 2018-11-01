<?php

declare(strict_types=1);

namespace Doctrine\RST\Builder;

final class State
{
    public const NO_PARSE = 1;
    public const PARSE    = 2;

    private function __construct()
    {
    }
}
