<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Renderers;

use Doctrine\RST\Nodes\ParagraphNode;
use Doctrine\RST\Renderers\NodeRenderer;
use function trim;

class ParagraphNodeRenderer implements NodeRenderer
{
    /** @var ParagraphNode */
    private $paragraphNode;

    public function __construct(ParagraphNode $paragraphNode)
    {
        $this->paragraphNode = $paragraphNode;
    }

    public function render() : string
    {
        $text = $this->paragraphNode->getValue()->render();

        if (trim($text) !== '') {
            return $text . "\n";
        }

        return '';
    }
}
