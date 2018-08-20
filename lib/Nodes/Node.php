<?php

declare(strict_types=1);

namespace Gregwar\RST\Nodes;

abstract class Node
{
    protected $value;

    public function __construct($value = null)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value) : void
    {
        $this->value = $value;
    }

    abstract public function render() : string;

    public function __toString()
    {
        return $this->render();
    }
}
