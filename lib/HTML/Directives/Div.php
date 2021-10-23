<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Directives;

use Doctrine\RST\Directives\SubDirective;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;

/**
 * Divs a sub document in a div with a given class
 */
final class Div extends SubDirective
{
    public function getName(): string
    {
        return 'div';
    }

    /**
     * @param string[] $options
     */
    public function processSub(
        Parser $parser,
        ?Node $document,
        string $variable,
        string $data,
        array $options
    ): ?Node {
        $divOpen = $parser->renderTemplate('div-open.html.twig', ['class' => $data]);

        return $parser->getNodeFactory()->createWrapperNode($document, $divOpen, '</div>');
    }
}
