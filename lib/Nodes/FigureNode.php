<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

abstract class FigureNode extends Node
{
    /** @var ImageNode */
    protected $image;

    /** @var null|Node */
    protected $document;

    public function __construct(ImageNode $image, ?Node $document = null)
    {
        parent::__construct();

        $this->image    = $image;
        $this->document = $document;
    }
}
