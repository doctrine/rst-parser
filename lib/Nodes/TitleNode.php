<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

abstract class TitleNode extends Node
{
    /** @var int */
    protected $level;

    /** @var string */
    protected $token;

    /** @var string */
    protected $target = '';

    public function __construct(Node $value, int $level, string $token)
    {
        parent::__construct($value);

        $this->level = $level;
        $this->token = $token;
    }

    public function getLevel() : int
    {
        return $this->level;
    }

    public function setTarget(string $target) : void
    {
        $this->target = $target;
    }

    public function getTarget() : string
    {
        return $this->target;
    }
}
