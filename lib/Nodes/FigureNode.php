<?php

declare(strict_types=1);

namespace Gregwar\RST\Nodes;

abstract class FigureNode extends Node
{
    protected $image;
    protected $document;

    public function __construct(ImageNode $image, $document = null)
    {
        $this->image    = $image;
        $this->document = $document;
    }
}
