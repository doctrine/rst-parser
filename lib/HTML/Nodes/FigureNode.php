<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Nodes;

use Doctrine\RST\Nodes\FigureNode as Base;
use function trim;

class FigureNode extends Base
{
    public function render() : string
    {
        $html  = '<figure>';
        $html .= $this->image->render();

        if ($this->document !== null) {
            $caption = trim($this->document->render());

            if ($caption !== '') {
                $html .= '<figcaption>' . $caption . '</figcaption>';
            }
        }

        $html .= '</figure>';

        return $html;
    }
}
