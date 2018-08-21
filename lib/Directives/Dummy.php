<?php

declare(strict_types=1);

namespace Gregwar\RST\Directives;

use Gregwar\RST\Directive;
use Gregwar\RST\Nodes\DummyNode;
use Gregwar\RST\Nodes\Node;
use Gregwar\RST\Parser;

class Dummy extends Directive
{
    public function getName() : string
    {
        return 'dummy';
    }

    /**
     * @param string[] $options
     */
    public function processNode(Parser $parser, string $variable, string $data, array $options) : ?Node
    {
        return new DummyNode(['data' => $data, 'options' => $options]);
    }
}
