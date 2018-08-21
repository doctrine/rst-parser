<?php

declare(strict_types=1);

namespace Gregwar\RST\LaTeX\Nodes;

use Gregwar\RST\Nodes\ParagraphNode as Base;
use function trim;

class ParagraphNode extends Base
{
    public function render() : string
    {
        $text = $this->value;

        if (trim($text) !== '') {
            return $text . "\n";
        }

        return '';
    }
}
