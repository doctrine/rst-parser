<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Nodes\FieldListNode;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

final class FieldListNodeRenderer implements NodeRenderer
{
    private FieldListNode $listNode;

    private TemplateRenderer $templateRenderer;

    public function __construct(FieldListNode $listNode, TemplateRenderer $templateRenderer)
    {
        $this->listNode         = $listNode;
        $this->templateRenderer = $templateRenderer;
    }

    public function render(): string
    {
        $template = 'field-list.html.twig';

        return $this->templateRenderer->render($template, ['listNode' => $this->listNode]);
    }
}
