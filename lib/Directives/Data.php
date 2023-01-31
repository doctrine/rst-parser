<?php

declare(strict_types=1);

namespace Doctrine\RST\Directives;

use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;

/**
 * A data directive passes the data to a template to be rendered.
 * ```
 * .. my-custom-directive:: With some Data
 * ```
 * Can be rendered by:
 * ```
 * [
 *     //...
 *     new DataDirective('my-custom-directive', 'directives/some-template.html.twig'),
 * ]
 */
final class Data extends Directive
{
    private string $name;
    private string $templateName;

    public function __construct(string $name, ?string $templateName = null)
    {
        $this->name         = $name;
        $this->templateName = $templateName ?? 'directives/' . $this->name . '.html.twig';
    }

    /** @param string[] $options */
    public function processNode(Parser $parser, string $variable, string $data, array $options): ?Node
    {
        $renderedCode = $parser->renderTemplate(
            $this->templateName,
            ['data' => $data]
        );

        return $parser->getNodeFactory()->createRawNode($renderedCode);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
