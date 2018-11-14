<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Directives;

use Doctrine\RST\Directive;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;
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
    public function process(
        Parser $parser,
        ?Node $node,
        string $variable,
        string $data,
        array $options
    ) : void {
        $document = $parser->getDocument();

        $document->addHeaderNode(
            $parser->getNodeFactory()->createRawNode('<title>' . htmlspecialchars($data) . '</title>')
        );

        if ($node === null) {
            return;
        }

        $document->addNode($node);
    }
}
