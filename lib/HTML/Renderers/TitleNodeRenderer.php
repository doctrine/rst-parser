<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\TitleNode;
use Doctrine\RST\Renderers\NodeRenderer;

class TitleNodeRenderer implements NodeRenderer
{
    /** @var TitleNode */
    private $titleNode;

    public function __construct(TitleNode $titleNode)
    {
        $this->titleNode = $titleNode;
    }

    public function render() : string
    {
        $value = $this->titleNode->getValue()->render();
        $level = $this->titleNode->getLevel();

        $anchor = Environment::slugify($value);

        return '<a id="' . $anchor . '"></a><h' . $level . '>' . $value . '</h' . $level . '>';
    }
}
