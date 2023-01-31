<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Nodes\ListNode;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

final class ListNodeRenderer implements NodeRenderer
{
    private ListNode $listNode;

    private TemplateRenderer $templateRenderer;

    public function __construct(ListNode $listNode, TemplateRenderer $templateRenderer)
    {
        $this->listNode         = $listNode;
        $this->templateRenderer = $templateRenderer;
    }

    public function render(): string
    {
        $template = 'bullet-list.html.twig';
        if ($this->listNode->isOrdered()) {
            $template = 'enumerated-list.html.twig';
        }

        return $this->templateRenderer->render($template, ['listNode' => $this->listNode]);
    }
}
