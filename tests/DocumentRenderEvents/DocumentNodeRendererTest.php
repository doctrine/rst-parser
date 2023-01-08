<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\DocumentRenderEvents;

use Doctrine\Common\EventManager;
use Doctrine\RST\Configuration;
use Doctrine\RST\Event\PostDocumentRenderEvent;
use Doctrine\RST\Event\PreDocumentRenderEvent;
use Doctrine\RST\HTML\Renderers\DocumentNodeRenderer;
use Doctrine\RST\Nodes\DocumentNode;
use Doctrine\RST\Templates\TemplateRenderer;
use Doctrine\Tests\RST\DocumentRenderEvents\Listener\DocumentRenderListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertEquals;

class DocumentNodeRendererTest extends TestCase
{
    /** @var Configuration|MockObject */
    private $configuration;
    /** @var DocumentNode|MockObject */
    private $document;
    /** @var TemplateRenderer|MockObject */
    private $templateRenderer;

    protected function setUp(): void
    {
        $this->configuration    = $this->createMock(Configuration::class);
        $this->document         = $this->createMock(DocumentNode::class);
        $this->templateRenderer = $this->createMock(TemplateRenderer::class);
        $this->document->method('getConfiguration')->willReturn($this->configuration);
    }

    public function testPreDocumentRenderEventOverridesTemplateNameAndParameters(): void
    {
        $template     = 'abc.html.twig';
        $parameter    = ['a' => 'bc'];
        $eventManager = new EventManager();
        $eventManager->addEventListener(
            [PreDocumentRenderEvent::PRE_DOCUMENT_RENDER],
            new DocumentRenderListener(
                $template,
                $parameter,
                ''
            )
        );
        $subject = new DocumentNodeRenderer($this->document, $this->templateRenderer, $eventManager);
        $this->templateRenderer
            ->expects(self::once())->method('render')
            ->with($template, $parameter);
        $subject->renderDocument();
    }

    public function testPostDocumentRenderEventOverridesHtml(): void
    {
        $html         = '<p>Lorem Ipsum</p>';
        $eventManager = new EventManager();
        $eventManager->addEventListener(
            [PostDocumentRenderEvent::POST_DOCUMENT_RENDER],
            new DocumentRenderListener(
                '',
                [],
                $html
            )
        );
        $subject = new DocumentNodeRenderer($this->document, $this->templateRenderer, $eventManager);
        $result  = $subject->renderDocument();
        assertEquals($html, $result);
    }
}
