<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Nodes;

use Doctrine\RST\Nodes\ParagraphNode as Base;
use function trim;

class ParagraphNode extends Base
{
    public function render() : string
    {
        $text = (string) $this->value;

        if (trim($text) !== '') {
            return $text . "\n";
        }

        return '';
    }
}
