<?php

declare(strict_types=1);

namespace Doctrine\RST\Directives;

use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;
use RuntimeException;

use function count;
use function explode;
use function htmlspecialchars;

/**
 * A wrapper directive passes the data to a template to be rendered. The content
 * of the directive is rendered and wrapped by the template
 * ```
 * ..  my-custom-directive:: className
 *
 *     Some **content** with some RST markup.
 * ```
 * Can be rendered by:
 * ```
 * [
 *     //...
 *     new WrapperDirective('my-custom-directive', 'directives/some-template.html.twig'),
 * ]
 * ```
 *
 * The template must contain exactly one string `|||` in the place that the
 * template should be split for wrapping the rendered content.
 *
 * For example:
 *
 * ```
 * <div class="{{ data }}">|||</div>
 * ```
 */
final class Wrapper extends SubDirective
{
    private string $name;
    private string $templateName;

    public function __construct(string $name, ?string $templateName = null)
    {
        $this->name         = $name;
        $this->templateName = $templateName ?? 'directives/' . $this->name . '.html.twig';
    }

    /** @param string[] $options */
    public function processSub(
        Parser $parser,
        ?Node $document,
        string $variable,
        string $data,
        array $options
    ): ?Node {
        $wrapperDiv = $parser->renderTemplate(
            $this->templateName,
            ['data' => htmlspecialchars($data)]
        );

        $wrapper = explode('|||', $wrapperDiv, 2);

        if (count($wrapper) < 2) {
            throw new RuntimeException('Template ' . $this->templateName . '  did not contain a mark for the wrapping position split (|||)');
        }

        return $parser->getNodeFactory()->createWrapperNode($document, $wrapper[0], $wrapper[1]);
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
