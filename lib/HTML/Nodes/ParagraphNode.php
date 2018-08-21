<?php

declare(strict_types=1);

namespace Gregwar\RST\HTML\Nodes;

use Gregwar\RST\Nodes\ParagraphNode as Base;
use function trim;

class ParagraphNode extends Base
{
    public function render() : string
    {
        $text = trim((string) $this->value);

        if ($text !== '') {
            return '<p>' . $text . '</p>';
        }

        return '';
    }
}
