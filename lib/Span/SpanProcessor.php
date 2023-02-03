<?php

declare(strict_types=1);

namespace Doctrine\RST\Span;

use Doctrine\RST\Environment;

use function mt_getrandmax;
use function preg_replace;
use function preg_replace_callback;
use function random_int;
use function sha1;
use function str_replace;
use function time;

final class SpanProcessor
{
    private Environment $environment;

    private string $span;

    private int $tokenId;

    private string $prefix;

    /** @var SpanToken[] */
    private array $tokens = [];

    public function __construct(Environment $environment, string $span)
    {
        $this->environment = $environment;
        $this->span        = $span;
        $this->tokenId     = 0;
        $this->prefix      = random_int(0, mt_getrandmax()) . '|' . time();
    }

    public function process(): string
    {
        $span = $this->replaceLiterals($this->span);

        $span = $this->replaceInterpretedText($span);

        $span = $this->replaceTitleLetters($span);

        $span = $this->replaceTextRoles($span);

        $span = $this->replaceLinks($span);

        $span = $this->replaceStandaloneHyperlinks($span);

        $span = $this->replaceStandaloneEmailAddresses($span);

        $span = $this->replaceEscapes($span);

        return $span;
    }

    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    public function setEnvironment(Environment $environment): void
    {
        $this->environment = $environment;
    }

    /** @return SpanToken[] */
    public function getTokens(): array
    {
        return $this->tokens;
    }

    public function getText(string $value): string
    {
        foreach ($this->tokens as $token) {
            $value = str_replace($token->getId(), $token->get('text'), $value);
        }

        return $value;
    }

    public function addToken(SpanToken $token): void
    {
        $this->tokens[$token->getId()] = $token;
    }

    private function replaceLiterals(string $span): string
    {
        $textRole =  $this->environment->getTextRole(SpanToken::TYPE_LITERAL);
        if ($textRole === null) {
            return $span;
        }

        return $textRole->replaceAndRegisterTokens($this, $span);
    }

    private function replaceInterpretedText(string $span): string
    {
        $textRole =  $this->environment->getTextRole(SpanToken::TYPE_INTERPRETED);
        if ($textRole === null) {
            return $span;
        }

        return $textRole->replaceAndRegisterTokens($this, $span);
    }

    private function replaceTitleLetters(string $span): string
    {
        foreach ($this->environment->getTitleLetters() as $level => $letter) {
            $span = (string) preg_replace_callback('/\#\\' . $letter . '/mUsi', fn (array $match): string => (string) $this->environment->getNumber($level), $span);
        }

        return $span;
    }

    private function replaceTextRoles(string $span): string
    {
        return (string) preg_replace_callback('/:([a-z0-9]+):`(.+)`/mUsi', function ($match): string {
            $textRoleName = $match[1];

            $text = $match[2];
            $id   = $this->generateId();

            $textRole =  $this->environment->getTextRole($textRoleName);
            $data     = $textRole->process($this->environment, $text);

            $this->addToken(new SpanToken($textRole, $id, $data));

            return $id;
        }, $span);
    }

    private function replaceLinks(string $span): string
    {
        $textRole =  $this->environment->getTextRole(SpanToken::TYPE_LINK);
        if ($textRole === null) {
            return $span;
        }

        return $textRole->replaceAndRegisterTokens($this, $span);
    }

    private function replaceStandaloneHyperlinks(string $span): string
    {
        // Replace standalone hyperlinks using a modified version of @gruber's
        // "Liberal Regex Pattern for all URLs", https://gist.github.com/gruber/249502
        $absoluteUriPattern = <<<'REGEX'
#(?i)\b
        (
            (?:
                [a-z][\w\-+.]+:
                (?:
                /{1,3}
                |
                [a-z0-9%]
            )
        )
        (?:
             [^\s()<>]+
             |
             \(([^\s()<>]+
             |
             (\([^\s()<>]+\)))*\)
        )+
        (?:
            \(([^\s()<>]+|(\([^\s()<>]+\)))*\)
            |
            [^\s\`!()\[\]{};:\'".,<>?«»“”‘’]
        )
    )#x
REGEX;

        // Standalone hyperlink callback
        $standaloneHyperlinkCallback = function ($match, $scheme = ''): string {
            $id  = $this->generateId();
            $url = $match[1];

            $textRole =  $this->environment->getTextRole(SpanToken::TYPE_LINK);

            $this->addToken(new SpanToken($textRole, $id, [
                'link' => $url,
                'url' => $scheme . $url,
            ]));

            return $id;
        };

        return (string) preg_replace_callback(
            $absoluteUriPattern,
            $standaloneHyperlinkCallback,
            $span
        );
    }

    private function replaceStandaloneEmailAddresses(string $span): string
    {
        // Replace standalone email addresses using a regex based on RFC 5322.
        $emailAddressPattern = <<<'REGEX'
/(
    (?:
    [a-z0-9!#$%&\'*+\/=?^_`{|}~-]+
    (?:
        \.[a-z0-9
        !#$%&\'*+\/=?^_`{|}~-]+
    )*
    |
    "(?:
        [\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]
        |
        \\[\x01-\x09\x0b\x0c\x0e-\x7f]
    )*")
    @
    (?:
        (?:
            [a-z0-9](?:
                [a-z0-9-]*[a-z0-9]
            )?\.
        )+[a-z0-9]
        (?:
            [a-z0-9-]*[a-z0-9]
        )?
        |
        \[(?:
            (?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.
        ){3}
        (?:
            25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:
                [\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f]
            )+
        )\]
    )
)/msix
REGEX;

        $standaloneEmailAddressCallback = function (array $match): string {
            $id  = $this->generateId();
            $url = $match[1];

            $textRole =  $this->environment->getTextRole(SpanToken::TYPE_LINK);

            $this->addToken(new SpanToken($textRole, $id, [
                'link' => $url,
                'url' =>  'mailto:' . $url,
            ]));

            return $id;
        };

        return (string) preg_replace_callback(
            $emailAddressPattern,
            $standaloneEmailAddressCallback,
            $span
        );
    }

    /**
     * Removes every backslash that is not escaped by a preceding backslash.
     */
    private function replaceEscapes(string $span): string
    {
        return preg_replace('/(?<!\\\\)\\\\/', '', $span);
    }

    public function generateId(): string
    {
        $this->tokenId++;

        return sha1($this->prefix . '|' . $this->tokenId);
    }
}
