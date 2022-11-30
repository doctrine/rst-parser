<?php

declare(strict_types=1);

namespace Doctrine\RST\Directives;

use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\WrapperNode;
use Doctrine\RST\Parser;

use function sprintf;

class Admonition extends SubDirective
{
    /** @var string */
    private $name;

    /** @var string */
    private $backgroundColor;

    /** @var string */
    private $textColor;

    /** @var string */
    private $icon;

    public function __construct(string $name, string $backgroundColor, string $textColor, string $icon)
    {
        $this->name            = $name;
        $this->backgroundColor = $backgroundColor;
        $this->textColor       = $textColor;
        $this->icon            = $icon;
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
        return new WrapperNode($document, sprintf(
            '<div class="alert %s-admonition %s %s border"><table width="100%%"><tr><td width="10" class="align-top"><i class="%s mr-2"></i></td><td>',
            $this->name,
            $this->backgroundColor,
            $this->textColor,
            $this->icon
        ), '</td></tr></table></div>');
    }
}
