<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Directives;

use Doctrine\RST\Directives\Directive;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;

/**
 * Add a meta title to the document
 *
 * .. title:: Page title
 */
final class Title extends Directive
{
    public function getName(): string
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
    ): void {
        $document = $parser->getDocument();

        $title = $parser->renderTemplate('title.html.twig', ['title' => $data]);

        $document->addHeaderNode(
            $parser->getNodeFactory()->createRawNode($title)
        );

        if ($node === null) {
            return;
        }

        $document->addNode($node);
    }
}
