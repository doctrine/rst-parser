<?php

declare(strict_types=1);

namespace Doctrine\RST\Event;

use Doctrine\Common\EventArgs;
use Doctrine\RST\Configuration;
use Doctrine\RST\Nodes\DocumentNode;

use function sprintf;

/**
 * This event is called in the DocumentNodeRenderer before the document Node is
 * rendered. The event can be used to influence the parameters sent to the
 * template and or the template name.
 */
final class PreDocumentRenderEvent extends EventArgs
{
    public const PRE_DOCUMENT_RENDER = 'preDocumentRender';

    private Configuration $configuration;
    private DocumentNode $documentNode;
    /** @var array|mixed[]  */
    private array $parameters;
    private string $templateName;

    /** @param array<string, mixed> $parameters */
    public function __construct(
        Configuration $configuration,
        DocumentNode $documentNode,
        array $parameters,
        string $templateName = 'document.%s.twig'
    ) {
        $this->configuration = $configuration;
        $this->documentNode  = $documentNode;
        $this->parameters    = $parameters;
        $this->templateName  = sprintf($templateName, $this->configuration->getFileExtension());
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function getDocumentNode(): DocumentNode
    {
        return $this->documentNode;
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

    /** @param array<string, mixed> $parameters */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function setTemplateName(string $templateName): void
    {
        $this->templateName = $templateName;
    }
}
