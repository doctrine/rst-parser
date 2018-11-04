<?php

declare(strict_types=1);

namespace Doctrine\RST\Parser;

use Doctrine\RST\Parser;
use function array_map;
use function count;
use function explode;
use function preg_match;
use function strlen;
use function substr;
use function trim;

class LineDataParser
{
    /** @var Parser */
    private $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

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
                $line[$i] === '*' || $line[$i] === '-' ? false : true,
                $depth,
                [$match[4]]
            );
        } elseif (strlen($line) === 1 && $line[0] === '-') {
            return new ListLine(
                $line[$i],
                $line[$i] === '*' || $line[$i] === '-' ? false : true,
                $depth,
                ['']
            );
        }

        return null;
    }

    /**
     * @param string[] $lines
     */
    public function parseDefinitionList(array $lines) : DefinitionList
    {
        $definitionList     = [];
        $definitionListTerm = null;
        $currentDefinition  = null;

        foreach ($lines as $key => $line) {
            // term definition line
            if ($definitionListTerm !== null && substr($line, 0, 4) === '    ') {
                $definition = trim($line);

                $currentDefinition .= $definition . ' ';

            // non empty string
            } elseif (trim($line) !== '') {
                // we are starting a new term so if we have an existing
                // term with definitions, add it to the definition list
                if ($definitionListTerm !== null) {
                    $definitionList[] = new DefinitionListTerm(
                        $definitionListTerm['term'],
                        $definitionListTerm['classifiers'],
                        $definitionListTerm['definitions']
                    );
                }

                $parts = explode(':', trim($line));

                $term = $parts[0];
                unset($parts[0]);

                $classifiers = array_map(function (string $classifier) {
                    return $this->parser->createSpan($classifier);
                }, array_map('trim', $parts));

                $definitionListTerm = [
                    'term' => $this->parser->createSpan($term),
                    'classifiers' => $classifiers,
                    'definitions' => [],
                ];

            // last line
            } elseif ($definitionListTerm !== null && trim($line) === '' && count($lines) - 1 === $key) {
                if ($currentDefinition !== null) {
                    $definitionListTerm['definitions'][] = $this->parser->createSpan($currentDefinition);

                    $currentDefinition = null;
                }

                $definitionList[] = new DefinitionListTerm(
                    $definitionListTerm['term'],
                    $definitionListTerm['classifiers'],
                    $definitionListTerm['definitions']
                );

            // empty line, start of a new definition for the current term
            } elseif ($currentDefinition !== null && $definitionListTerm !== null && trim($line) === '') {
                $definitionListTerm['definitions'][] = $this->parser->createSpan($currentDefinition);

                $currentDefinition = null;
            }
        }

        return new DefinitionList($definitionList);
    }
}
