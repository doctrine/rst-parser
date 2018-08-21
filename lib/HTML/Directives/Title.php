<?php

declare(strict_types=1);

namespace Gregwar\RST\HTML\Directives;

use Gregwar\RST\Directive;
use Gregwar\RST\Nodes\Node;
use Gregwar\RST\Nodes\RawNode;
use Gregwar\RST\Parser;
use function htmlspecialchars;

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

        $document->addHeaderNode(new RawNode('<title>' . htmlspecialchars($data) . '</title>'));

        if ($node !== null) {
            return;
        }

        $document->addNode($node);
    }
}
