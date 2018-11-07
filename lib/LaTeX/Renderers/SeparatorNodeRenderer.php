<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Renderers;

use Doctrine\RST\Renderers\NodeRenderer;

class SeparatorNodeRenderer implements NodeRenderer
{
    public function render() : string
    {
        return '\\ \\';
    }
}
