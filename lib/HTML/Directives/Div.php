<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Directives;

use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;
use Doctrine\RST\SubDirective;

/**
 * Divs a sub document in a div with a given class
 */
class Div extends SubDirective
{
    public function getName() : string
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
    ) : ?Node {
        return $parser->getNodeFactory()->createWrapperNode($document, '<div class="' . $data . '">', '</div>');
    }
}
