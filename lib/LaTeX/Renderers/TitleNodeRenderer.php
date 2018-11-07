<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Renderers;

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
        $type = 'chapter';

        if ($this->titleNode->getLevel() > 1) {
            $type = 'section';

            for ($i = 2; $i < $this->titleNode->getLevel(); $i++) {
                $type = 'sub' . $type;
            }
        }

        return '\\' . $type . '{' . $this->titleNode->getValue()->render() . '}';
    }
}
