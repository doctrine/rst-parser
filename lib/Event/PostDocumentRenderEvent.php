<?php

declare(strict_types=1);

namespace Doctrine\RST\Event;

use Doctrine\Common\EventArgs;
use Doctrine\RST\Configuration;
use Doctrine\RST\Nodes\DocumentNode;
use Doctrine\RST\Templates\TemplateRenderer;

use function sprintf;

/**
 * This event is called in the DocumentNodeRenderer after the content of
 * the document got rendered and before the HTML is returned. It can be used
 * to postprocess or change the HTML that will be written to the file.
 */
final class PostDocumentRenderEvent extends EventArgs
{
    public const POST_DOCUMENT_RENDER = 'postDocumentRender';

    private Configuration $configuration;
    private DocumentNode $documentNode;
    private TemplateRenderer $templateRenderer;
    private string $html;
    /** @var array<string, mixed>  */
    private array $parameters;
    private string $templateName;

    /** @param array<string, mixed> $parameters */
    public function __construct(
        Configuration $configuration,
        DocumentNode $documentNode,
        TemplateRenderer $templateRenderer,
        string $html,
        array $parameters,
        string $templateName = 'document.%s.twig'
    ) {
        $this->configuration    = $configuration;
        $this->documentNode     = $documentNode;
        $this->templateRenderer = $templateRenderer;
        $this->html             = $html;
        $this->parameters       = $parameters;
        $this->templateName     = sprintf($templateName, $this->configuration->getFileExtension());
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function getDocumentNode(): DocumentNode
    {
        return $this->documentNode;
    }

    public function getTemplateRenderer(): TemplateRenderer
    {
        return $this->templateRenderer;
    }

    public function getHtml(): string
    {
        return $this->html;
    }

    /** @return array<string, mixed> */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    public function setHtml(string $html): void
    {
        $this->html = $html;
    }
}
