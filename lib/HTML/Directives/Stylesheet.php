<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Directives;

use Doctrine\RST\Directive;
use Doctrine\RST\HTML\Document;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;

/**
 * Adds a stylesheet to a document, example:
 *
 * .. stylesheet:: style.css
 */
class Stylesheet extends Directive
{
    public function getName() : string
    {
        return 'stylesheet';
    }

    /**
     * @param string[] $options
     */
    public function process(Parser $parser, ?Node $node, string $variable, string $data, array $options) : void
    {
        /** @var Document $document */
        $document = $parser->getDocument();

        $document->addCss($data);

        if ($node === null) {
            return;
        }

        $document->addNode($node);
    }
}
