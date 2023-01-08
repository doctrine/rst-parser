<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\DocumentRenderEvents\Listener;

use Doctrine\RST\Event\PostDocumentRenderEvent;
use Doctrine\RST\Event\PreDocumentRenderEvent;

class DocumentRenderListener
{
    private string $templateName;
    /** @var array<string, string> */
    private array $parameters;
    private string $html;

    /** @param string[] $parameters */
    public function __construct(
        string $templateName,
        array $parameters,
        string $html
    ) {
        $this->templateName = $templateName;
        $this->parameters   = $parameters;
        $this->html         = $html;
    }

    public function preDocumentRender(PreDocumentRenderEvent $event): void
    {
        $event->setTemplateName($this->templateName);
        $event->setParameters($this->parameters);
    }

    public function postDocumentRender(PostDocumentRenderEvent $event): void
    {
        $event->setHtml($this->html);
    }
}
