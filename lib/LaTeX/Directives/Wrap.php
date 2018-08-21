<?php

declare(strict_types=1);

namespace Gregwar\RST\LaTeX\Directives;

use Gregwar\RST\Nodes\Node;
use Gregwar\RST\Parser;
use Gregwar\RST\SubDirective;

/**
 * Wraps a sub document in a div with a given class
 */
class Wrap extends SubDirective
{
    /** @var string */
    protected $class;

    public function __construct(string $class)
    {
        $this->class = $class;
    }

    public function getName() : string
    {
        return $this->class;
    }

    /**
     * @param string[] $options
     */
    public function processSub(Parser $parser, ?Node $document, string $variable, string $data, array $options) : ?Node
    {
        return $document;
    }
}
