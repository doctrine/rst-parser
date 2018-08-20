<?php

declare(strict_types=1);

namespace Gregwar\RST\LaTeX\Directives;

use Gregwar\RST\Directive;
use Gregwar\RST\Nodes\RawNode;
use Gregwar\RST\Parser;

/**
 * Add a meta title to the document
 *
 * .. title:: Page title
 */
class Title extends Directive
{
    public function getName() : string
    {
        return 'title';
    }

    public function process(Parser $parser, $node, $variable, $data, array $options) : void
    {
        $document = $parser->getDocument();

        $document->addHeaderNode(new RawNode('\title{' . $data . '}'));

        if (! $node) {
            return;
        }

        $document->addNode($node);
    }
}
