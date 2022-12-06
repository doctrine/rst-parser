<?php

declare(strict_types=1);

namespace Doctrine\RST\Directives;

use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;
use RuntimeException;

use function count;
use function explode;

class Admonition extends SubDirective
{
    /** @var string */
    private $name;

    /** @var string */
    private $text;

    public function __construct(string $name, string $text)
    {
        $this->name = $name;
        $this->text = $text;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /** @param string[] $options */
    public function processSub(
        Parser $parser,
        ?Node $document,
        string $variable,
        string $data,
        array $options
    ): ?Node {
        if ($document === null) {
            throw new RuntimeException('Content expected, none found.');
        }

        $wrapperDiv = $parser->renderTemplate(
            'directives/admonition.html.twig',
            [
                'name' => $this->name,
                'text' => $this->text,
                'class' => $options['class'] ?? null,
            ]
        );

        $wrapper = explode('|||', $wrapperDiv, 2);

        if (count($wrapper) < 2) {
            throw new RuntimeException('Template did not contain a mark for the wrapping position split (|||)');
        }

        return $parser->getNodeFactory()->createWrapperNode($document, $wrapper[0], $wrapper[1]);
    }
}
