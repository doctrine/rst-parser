<?php

declare(strict_types=1);

namespace Doctrine\RST\Parser;

use function in_array;
use function preg_match;
use function strlen;
use function strpos;
use function trim;

class LineChecker
{
    private const HEADER_LETTERS = ['=', '-', '~', '*', '+', '^', '"', '.', '`', "'", '_', '#', ':'];

    /** @var LineDataParser */
    private $lineParser;

    public function __construct(LineDataParser $lineParser)
    {
        $this->lineParser = $lineParser;
    }

    public function isSpecialLine(string $line): ?string
    {
        if (strlen($line) < 2) {
            return null;
        }

        $letter = $line[0];

        if (! in_array($letter, self::HEADER_LETTERS, true)) {
            return null;
        }

        for ($i = 1; $i < strlen($line); $i++) {
            if ($line[$i] !== $letter) {
                return null;
            }
        }

        return $letter;
    }

    public function isListLine(string $line, bool $isCode): bool
    {
        $listLine = $this->lineParser->parseListLine($line);

        if ($listLine !== null) {
            return $listLine->getDepth() === 0 || ! $isCode;
        }

        return false;
    }

    /**
     * Is this line "indented"?
     *
     * A blank line also counts as a "block" line, as it
     * may be the empty line between, for example, a
     * ".. note::" directive and the indented content on the
     * next lines.
     *
     * @param int $minIndent can be used to require a specific level of
     *                       indentation for non-blank lines (number of spaces)
     */
    public function isBlockLine(string $line, int $minIndent = 1): bool
    {
        return trim($line) === '' || $this->isIndented($line, $minIndent);
    }

    public function isComment(string $line): bool
    {
        return preg_match('/^\.\. (.*)$/mUsi', $line) > 0;
    }

    public function isDirective(string $line): bool
    {
        return preg_match('/^\.\. (\|(.+)\| |)([^\s]+)::( (.*)|)$/mUsi', $line) > 0;
    }

    /**
     * Check if line is an indented one.
     *
     * This does *not* include blank lines, use {@see isBlockLine()} to check
     * for blank or indented lines.
     *
     * @param int $minIndent can be used to require a specific level of indentation (number of spaces)
     */
    public function isIndented(string $line, int $minIndent = 1): bool
    {
        return strpos($line, str_repeat(' ', $minIndent)) === 0;
    }

    /**
     * Checks if the current line can be considered part of the definition list.
     *
     * Either the current line, or the next line must be indented to be considered
     * definition.
     *
     * @see https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#definition-lists
     */
    public function isDefinitionListEnded(string $line, string $nextLine): bool
    {
        if (trim($line) === '') {
            return false;
        }

        if ($this->isIndented($line)) {
            return false;
        }

        return ! $this->isIndented($nextLine);
    }
}
