<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\Common\EventManager;
use Doctrine\RST\Event\PostDocumentRenderEvent;
use Doctrine\RST\Event\PreDocumentRenderEvent;
use Doctrine\RST\Nodes\DocumentNode;
use Doctrine\RST\Renderers\DocumentNodeRenderer as BaseDocumentRender;
use Doctrine\RST\Renderers\FullDocumentNodeRenderer;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

final class DocumentNodeRenderer implements NodeRenderer, FullDocumentNodeRenderer
{
    private DocumentNode $document;
    private TemplateRenderer $templateRenderer;
    private EventManager $eventManager;

    public function __construct(DocumentNode $document, TemplateRenderer $templateRenderer, ?EventManager $eventManager = null)
    {
        $this->document         = $document;
        $this->templateRenderer = $templateRenderer;
        $this->eventManager     = $eventManager ?? $this->document->getConfiguration()->getEventManager();
    }

    public function render(): string
    {
        return (new BaseDocumentRender($this->document))->render();
    }

    public function renderDocument(): string
    {
        $headerNodes = '';

        foreach ($this->document->getHeaderNodes() as $node) {
            $headerNodes .= $node->render() . "\n";
        }

        $preDocumentRenderEvent = new PreDocumentRenderEvent(
            $this->document->getConfiguration(),
            $this->document,
            [
                'headerNodes' => $headerNodes,
                'bodyNodes' => $this->render(),
            ],
            'document.%s.twig'
        );
        $this->eventManager->dispatchEvent(
            PreDocumentRenderEvent::PRE_DOCUMENT_RENDER,
            $preDocumentRenderEvent
        );
        $html = $this->templateRenderer->render($preDocumentRenderEvent->getTemplateName(), $preDocumentRenderEvent->getParameters());

        $postDocumentRenderEvent = new PostDocumentRenderEvent(
            $this->document->getConfiguration(),
            $this->document,
            $this->templateRenderer,
            $html,
            $preDocumentRenderEvent->getParameters(),
            $preDocumentRenderEvent->getTemplateName()
        );
        $this->eventManager->dispatchEvent(
            PostDocumentRenderEvent::POST_DOCUMENT_RENDER,
            $postDocumentRenderEvent
        );

        return $postDocumentRenderEvent->getHtml();
    }
}
