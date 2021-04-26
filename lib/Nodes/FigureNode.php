<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

final class FigureNode extends Node
{
    /** @var ImageNode */
    private $image;

    /** @var Node|null */
    private $document;

    public function __construct(ImageNode $image, ?Node $document = null)
    {
        parent::__construct();

        $this->image    = $image;
        $this->document = $document;
    }

    public function getImage(): ImageNode
    {
        return $this->image;
    }

    public function getDocument(): ?Node
    {
        return $this->document;
    }
}
