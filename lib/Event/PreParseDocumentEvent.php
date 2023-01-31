<?php

declare(strict_types=1);

namespace Doctrine\RST\Event;

use Doctrine\Common\EventArgs;
use Doctrine\RST\Parser;

final class PreParseDocumentEvent extends EventArgs
{
    public const PRE_PARSE_DOCUMENT = 'preParseDocument';

    private Parser $parser;

    private string $contents;

    public function __construct(Parser $parser, string $contents)
    {
        $this->parser   = $parser;
        $this->contents = $contents;
    }

    public function getParser(): Parser
    {
        return $this->parser;
    }

    public function setContents(string $contents): void
    {
        $this->contents = $contents;
    }

    public function getContents(): string
    {
        return $this->contents;
    }
}
