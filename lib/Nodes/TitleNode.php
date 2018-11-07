<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

class TitleNode extends Node
{
    /** @var Node */
    protected $value;

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

    public function getValue() : Node
    {
        return $this->value;
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
