<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

abstract class MetaNode extends Node
{
    /** @var string */
    protected $key;

    /** @var string */
    protected $value;

    public function __construct(string $key, string $value)
    {
        parent::__construct();

        $this->key   = $key;
        $this->value = $value;
    }
}
