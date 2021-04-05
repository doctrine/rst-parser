<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Renderers;

use Doctrine\RST\Nodes\ListNode;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

final class ListNodeRenderer implements NodeRenderer
{
    /** @var ListNode */
    private $listNode;

    /** @var TemplateRenderer */
    private $templateRenderer;

    public function __construct(ListNode $listNode, TemplateRenderer $templateRenderer)
    {
        $this->listNode         = $listNode;
        $this->templateRenderer = $templateRenderer;
    }

    public function render(): string
    {
        $template = 'bullet-list.tex.twig';
        if ($this->listNode->isOrdered()) {
            $template = 'enumerated-list.tex.twig';
        }

        return $this->templateRenderer->render($template, [
            'listNode' => $this->listNode,
        ]);
    }
}
