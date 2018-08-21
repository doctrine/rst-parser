<?php

declare(strict_types=1);

namespace Gregwar\RST\LaTeX\Directives;

use Gregwar\RST\Directive;
use Gregwar\RST\Nodes\Node;
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

    /**
     * @param string[] $options
     */
    public function process(Parser $parser, ?Node $node, string $variable, string $data, array $options) : void
    {
        $document = $parser->getDocument();

        $document->addHeaderNode(new RawNode('\title{' . $data . '}'));

        if ($node === null) {
            return;
        }

        $document->addNode($node);
    }
}
