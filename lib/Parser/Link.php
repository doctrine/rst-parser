<?php

declare(strict_types=1);

namespace Doctrine\RST\Parser;

/**
 * An explicit link *target* (internal or external).
 *
 * https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#hyperlink-targets
 */
final class Link
{
    public const TYPE_LINK   = 'link';
    public const TYPE_ANCHOR = 'anchor';

    /** @var string */
    private $name;

    /** @var string */
    private $url;

    /** @var string */
    private $type;

    public function __construct(string $name, string $url, string $type)
    {
        $this->name = $name;
        $this->url  = $url;
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
