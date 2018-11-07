<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

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
        $text = trim($this->paragraphNode->getValue()->render());

        if ($text !== '') {
            return '<p>' . $text . '</p>';
        }

        return '';
    }
}
