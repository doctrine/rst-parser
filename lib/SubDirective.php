<?php

declare(strict_types=1);

namespace Gregwar\RST;

use Gregwar\RST\Nodes\CodeNode;

/**
 * A directive that parses the sub block and call the processSub that can
 * be overloaded, like :
 *
 * .. sub-directive::
 *      Some block of code
 *
 *      You can imagine anything here, like adding *emphasis*, lists or
 *      titles
 */
abstract class SubDirective extends Directive
{
    /**
     * Process a directive that should parces the next node as a "sub" document
     */
    final public function process(Parser $parser, $node, $variable, $data, array $options) : void
    {
        $subParser = $parser->getSubParser();

        if ($node instanceof CodeNode) {
            $document = $subParser->parseLocal($node->getValue());
        } else {
            $document = $node;
        }

        $newNode = $this->processSub($parser, $document, $variable, $data, $options);

        if (! $newNode) {
            return;
        }

        if ($variable) {
            $parser->getEnvironment()->setVariable($variable, $newNode);
        } else {
            $parser->getDocument()->addNode($newNode);
        }
    }

    /**
     * Process a sub directive
     */
    public function processSub(Parser $parser, $document, $variable, $data, array $options)
    {
        return null;
    }

    public function wantCode() : bool
    {
        return true;
    }
}
