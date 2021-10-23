<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

use Doctrine\RST\Environment;

class TitleNode extends Node
{
    /** @var SpanNode */
    protected $value;

    /** @var int */
    private $level;

    /** @var string */
    protected $token;

    /** @var string */
    private $id;

    /** @var string */
    private $target = '';

    public function __construct(Node $value, int $level, string $token)
    {
        parent::__construct($value);

        $this->level = $level;
        $this->token = $token;
        $this->id    = Environment::slugify($this->value->getText());
    }

    public function getValue(): SpanNode
    {
        return $this->value;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setTarget(string $target): void
    {
        $this->target = $target;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
