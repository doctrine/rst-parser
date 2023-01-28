<?php

declare(strict_types=1);

namespace Doctrine\RST\TextRoles;

use Doctrine\RST\Environment;
use function htmlspecialchars;
use function sprintf;

class WrapperTextRole extends TextRole
{
    private string $name;
    private string $wrap;

    /**
     * @param string   $wrap    A sprintf string containing one marker for the text
     * @param String[] $aliases Aliases for the name
     */
    public function __construct(string $name, string $wrap, array $aliases = [])
    {
        $this->name = $name;
        $this->wrap = $wrap;
        $this->setAliases($aliases);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function process(Environment $environment, string $text): string
    {
        return sprintf($this->wrap, htmlspecialchars($text));
    }
}
