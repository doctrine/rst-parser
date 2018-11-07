<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

class CallableNode extends Node
{
    /** @var callable */
    private $callable;

    public function __construct(callable $callable)
    {
        parent::__construct();

        $this->callable = $callable;
    }

    protected function doRender() : string
    {
        /** @var callable $callable */
        $callable = $this->callable;

        return $callable();
    }
}
