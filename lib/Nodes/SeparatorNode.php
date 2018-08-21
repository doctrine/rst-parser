<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

abstract class SeparatorNode extends Node
{
    /** @var int */
    protected $level;

    public function __construct(int $level)
    {
        parent::__construct();

        $this->level = $level;
    }
}
