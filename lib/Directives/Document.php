<?php

declare(strict_types=1);

namespace Doctrine\RST\Directives;

use Doctrine\RST\Directive;
use Doctrine\RST\Nodes\DocumentNode;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;

/**
 * Tell that this is a document, in the case of LaTeX for instance,
 * this will mark the current document as one of the master document that
 * should be compiled
 */
class Document extends Directive
{
    public function getName() : string
    {
        return 'document';
    }

    /**
     * @param string[] $options
     */
    public function processNode(
        Parser $parser,
        string $variable,
        string $data,
        array $options
    ) : ?Node {
        return new DocumentNode();
    }
}
