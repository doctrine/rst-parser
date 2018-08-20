<?php

declare(strict_types=1);

namespace Gregwar\RST\Directives;

use Gregwar\RST\Directive;
use Gregwar\RST\Nodes\TitleNode;
use Gregwar\RST\Parser;

/**
 * This sets a new target for a following title, this can be used to change
 * its link
 */
class RedirectionTitle extends Directive
{
    public function getName() : string
    {
        return 'redirection-title';
    }

    public function process(Parser $parser, $node, $variable, $data, array $options) : void
    {
        $document = $parser->getDocument();

        if (! $node) {
            return;
        }

        if ($node instanceof TitleNode) {
            $node->setTarget($data);
        }
        $document->addNode($node);
    }
}
