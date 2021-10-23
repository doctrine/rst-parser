<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

class SeparatorNode extends Node
{
    /** @var int */
    private $level;

    public function __construct(int $level)
    {
        parent::__construct();

        $this->level = $level;
    }

    public function getLevel(): int
    {
        return $this->level;
    }
}
