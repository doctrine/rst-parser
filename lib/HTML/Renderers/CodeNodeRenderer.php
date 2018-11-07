<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Renderers\NodeRenderer;
use function htmlspecialchars;

class CodeNodeRenderer implements NodeRenderer
{
    /** @var CodeNode */
    private $codeNode;

    public function __construct(CodeNode $codeNode)
    {
        $this->codeNode = $codeNode;
    }

    public function render() : string
    {
        $value = $this->codeNode->getValue();

        if ($this->codeNode->isRaw()) {
            return $value;
        }

        $language = $this->codeNode->getLanguage();

        return '<pre><code class="' . $language . '">' . htmlspecialchars($value) . '</code></pre>';
    }
}
