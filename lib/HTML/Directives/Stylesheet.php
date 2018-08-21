<?php

declare(strict_types=1);

namespace Gregwar\RST\HTML\Directives;

use Gregwar\RST\Directive;
use Gregwar\RST\HTML\Document;
use Gregwar\RST\Nodes\Node;
use Gregwar\RST\Parser;

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
