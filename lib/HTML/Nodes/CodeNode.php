<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Nodes;

use Doctrine\RST\Nodes\CodeNode as Base;
use function htmlspecialchars;

class CodeNode extends Base
{
    protected function doRender() : string
    {
        if ($this->raw) {
            return (string) $this->value;
        }

        return '<pre><code class="' . $this->language . '">' . htmlspecialchars((string) $this->value) . '</code></pre>';
    }
}
