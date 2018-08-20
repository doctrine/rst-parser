<?php

declare(strict_types=1);

namespace Gregwar\RST\Directives;

use Gregwar\RST\Directive;
use Gregwar\RST\Nodes\DummyNode;
use Gregwar\RST\Parser;

class Dummy extends Directive
{
    public function getName() : string
    {
        return 'dummy';
    }

    public function processNode(Parser $parser, $variable, $data, array $options)
    {
        return new DummyNode(['data' => $data, 'options' => $options]);
    }
}
