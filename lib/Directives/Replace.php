<?php

declare(strict_types=1);

namespace Gregwar\RST\Directives;

use Gregwar\RST\Directive;
use Gregwar\RST\Nodes\Node;
use Gregwar\RST\Parser;

/**
 * The Replace directive will set the variables for the spans
 *
 * .. |test| replace:: The Test String!
 */
class Replace extends Directive
{
    public function getName() : string
    {
        return 'replace';
    }

    /**
     * @param string[] $options
     */
    public function processNode(Parser $parser, string $variable, string $data, array $options) : ?Node
    {
        return $parser->createSpan($data);
    }
}
