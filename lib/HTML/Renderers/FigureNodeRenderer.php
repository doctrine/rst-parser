<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Nodes\FigureNode;
use Doctrine\RST\Renderers\NodeRenderer;
use function trim;

class FigureNodeRenderer implements NodeRenderer
{
    /** @var FigureNode */
    private $figureNode;

    public function __construct(FigureNode $figureNode)
    {
        $this->figureNode = $figureNode;
    }

    public function render() : string
    {
        $html  = '<figure>';
        $html .= $this->figureNode->getImage()->render();

        $document = $this->figureNode->getDocument();

        if ($document !== null) {
            $caption = trim($document->render());

            if ($caption !== '') {
                $html .= '<figcaption>' . $caption . '</figcaption>';
            }
        }

        return $html .= '</figure>';
    }
}
