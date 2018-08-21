<?php

declare(strict_types=1);

namespace Doctrine\RST\Directives;

use Doctrine\RST\Directive;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\TitleNode;
use Doctrine\RST\Parser;

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

    /**
     * @param string[] $options
     */
    public function process(Parser $parser, ?Node $node, string $variable, string $data, array $options) : void
    {
        $document = $parser->getDocument();

        if ($node === null) {
            return;
        }

        if ($node instanceof TitleNode) {
            $node->setTarget($data);
        }

        $document->addNode($node);
    }
}
