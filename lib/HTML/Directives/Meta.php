<?php

declare(strict_types=1);

namespace Gregwar\RST\HTML\Directives;

use Gregwar\RST\Directive;
use Gregwar\RST\HTML\Nodes\MetaNode;
use Gregwar\RST\Parser;

/**
 * Add a meta information:
 *
 * .. meta::
 *      :key: value
 */
class Meta extends Directive
{
    public function getName() : string
    {
        return 'meta';
    }

    public function process(Parser $parser, $node, $variable, $data, array $options) : void
    {
        $document = $parser->getDocument();

        foreach ($options as $key => $value) {
            $meta = new MetaNode($key, $value);
            $document->addHeaderNode($meta);
        }

        if (! $node) {
            return;
        }

        $document->addNode($node);
    }
}
