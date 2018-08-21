<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

abstract class Node
{
    /** @var Node|string|null */
    protected $value;

    /**
     * @param Node|string|null $value
     */
    public function __construct($value = null)
    {
        $this->value = $value;
    }

    /**
     * @return Node|string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param Node|string|null $value
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
