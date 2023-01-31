<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

final class CodeNodeRenderer implements NodeRenderer
{
    private CodeNode $codeNode;

    private TemplateRenderer $templateRenderer;

    public function __construct(CodeNode $codeNode, TemplateRenderer $templateRenderer)
    {
        $this->codeNode         = $codeNode;
        $this->templateRenderer = $templateRenderer;
    }

    public function render(): string
    {
        if ($this->codeNode->isRaw()) {
            return $this->codeNode->getValue();
        }

        return $this->templateRenderer->render('code.html.twig', [
            'codeNode' => $this->codeNode,
        ]);
    }
}
