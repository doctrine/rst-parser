<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Renderers\NodeRenderer;

class SeparatorNodeRenderer implements NodeRenderer
{
    public function render() : string
    {
        return '<hr />';
    }
}
