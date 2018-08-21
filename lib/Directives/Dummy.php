<?php

declare(strict_types=1);

namespace Doctrine\RST\Directives;

use Doctrine\RST\Directive;
use Doctrine\RST\Nodes\DummyNode;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;

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
