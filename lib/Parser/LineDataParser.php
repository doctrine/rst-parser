<?php

declare(strict_types=1);

namespace Doctrine\RST\Parser;

use function preg_match;
use function strlen;
use function trim;

class LineDataParser
{
    public function parseLink(string $line) : ?Link
    {
        // Links
        if (preg_match('/^\.\. _`(.+)`: (.+)$/mUsi', $line, $match) > 0) {
            return new Link($match[1], $match[2], Link::TYPE_LINK);
        }

        if (preg_match('/^\.\. _(.+): (.+)$/mUsi', $line, $match) > 0) {
            return new Link($match[1], $match[2], Link::TYPE_LINK);
        }

        // Short anonymous links
        if (preg_match('/^__ (.+)$/mUsi', trim($line), $match) > 0) {
            $url = $match[1];

            return new Link('_', $url, Link::TYPE_LINK);
        }

        // Anchor link
        if (preg_match('/^\.\. _(.+):$/mUsi', trim($line), $match) > 0) {
            $anchor = $match[1];

            return new Link($anchor, '#' . $anchor, Link::TYPE_ANCHOR);
        }

        return null;
    }

    public function parseDirectiveOption(string $line) : ?DirectiveOption
    {
        if (preg_match('/^(\s+):(.+): (.*)$/mUsi', $line, $match) > 0) {
            return new DirectiveOption($match[2], trim($match[3]));
        }

        if (preg_match('/^(\s+):(.+):(\s*)$/mUsi', $line, $match) > 0) {
            $value = trim($match[3]);

            return new DirectiveOption($match[2], true);
        }

        return null;
    }

    public function parseDirective(string $line) : ?Directive
    {
        if (preg_match('/^\.\. (\|(.+)\| |)([^\s]+)::( (.*)|)$/mUsi', $line, $match) > 0) {
            return new Directive(
                $match[2],
                $match[3],
                trim($match[4])
            );
        }

        return null;
    }

    public function parseListLine(string $line) : ?ListLine
    {
        $depth = 0;
        $i     = 0;

        for ($i = 0; $i < strlen($line); $i++) {
            $char = $line[$i];

            if ($char === ' ') {
                $depth++;
            } elseif ($char === "\t") {
                $depth += 2;
            } else {
                break;
            }
        }

        if (preg_match('/^((\*|\-)|([\d#]+)\.) (.+)$/', trim($line), $match) > 0) {
            return new ListLine(
                $line[$i],
                ($line[$i] === '*' || $line[$i] === '-') ? false : true,
                $depth,
                [$match[4]]
            );
        } elseif (strlen($line) === 1 && $line[0] === '-') {
            return new ListLine(
                $line[$i],
                ($line[$i] === '*' || $line[$i] === '-') ? false : true,
                $depth,
                ['']
            );
        }

        return null;
    }
}
