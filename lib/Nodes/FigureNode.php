<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

final class FigureNode extends Node
{
    private ImageNode $image;

    private ?Node $document = null;

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
