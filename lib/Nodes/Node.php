<?php

declare(strict_types=1);

namespace Gregwar\RST\Nodes;

abstract class Node
{
    /** @var Node|string */
    protected $value;

    /**
     * @param Node|string $value
     */
    public function __construct($value = null)
    {
        $this->value = $value;
    }

    /**
     * @return Node|string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param Node|string $value
     */
    public function setValue($value) : void
    {
        $this->value = $value;
    }

    abstract public function render() : string;

    public function __toString() : string
    {
        return $this->render();
    }
}
