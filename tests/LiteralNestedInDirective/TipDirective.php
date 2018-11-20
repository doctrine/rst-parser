<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\LiteralNestedInDirective;

use Doctrine\RST\Directives\SubDirective;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;

class TipDirective extends SubDirective
{
    /**
     * @param string[] $options
     */
    final public function processSub(Parser $parser, ?Node $document, string $variable, string $data, array $options) : ?Node
    {
        return $parser->getNodeFactory()->createWrapperNode($document, '<div class="tip">', '</div>');
    }

    /**
     * Get the directive name
     */
    public function getName() : string
    {
        return 'tip';
    }
}
