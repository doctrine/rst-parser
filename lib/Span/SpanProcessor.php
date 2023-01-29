<?php

declare(strict_types=1);

namespace Doctrine\RST\Span;

use Doctrine\RST\Environment;
use Doctrine\RST\Meta\LinkTarget;

use function mt_rand;
use function preg_match;
use function preg_match_all;
use function preg_replace;
use function preg_replace_callback;
use function sha1;
use function str_replace;
use function substr;
use function time;

final class SpanProcessor
{
    /** @var Environment */
    private $environment;

    /** @var string */
    private $span;

    /** @var int */
    private $tokenId;

    /** @var string */
    private $prefix;

    /** @var SpanToken[] */
    private $tokens = [];

    public function __construct(Environment $environment, string $span)
    {
        $this->environment = $environment;
        $this->span        = $span;
        $this->tokenId     = 0;
        $this->prefix      = mt_rand() . '|' . time();
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

    /** @param string[] $tokenData */
    private function addToken(string $type, string $id, array $tokenData): void
    {
        $this->tokens[$id] = new SpanToken($type, $id, $tokenData);
    }

    private function replaceLiterals(string $span): string
    {
        return (string) preg_replace_callback(
            '/``(.+)``(?!`)/mUsi',
            function (array $match): string {
                $id = $this->generateId();

                $this->addToken(SpanToken::TYPE_LITERAL, $id, [
                    'type' => 'literal',
                    'text' => $match[1],
                ]);

                return $id;
            },
            $span
        );
    }

    private function replaceInterpretedText(string $span): string
    {
        return (string) preg_replace_callback(
            '/(?<=^|\s)`(\S|\S\S|\S[^`]+\S)`(?!(:.+:|[_]))/mUsi',
            function (array $match): string {
                $id = $this->generateId();

                $this->addToken(SpanToken::TYPE_INTERPRETED, $id, [
                    'type' => SpanToken::TYPE_INTERPRETED,
                    'text' => $match[1],
                ]);

                return $id;
            },
            $span
        );
    }

    private function replaceTitleLetters(string $span): string
    {
        foreach ($this->environment->getTitleLetters() as $level => $letter) {
            $span = (string) preg_replace_callback('/\#\\' . $letter . '/mUsi', function (array $match) use ($level): string {
                return (string) $this->environment->getNumber($level);
            }, $span);
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

            $this->addToken(SpanToken::TYPE_TEXT_ROLE, $id, $data);

            return $id;
        }, $span);
    }

    private function replaceLinks(string $span): string
    {
        // Signaling anonymous names
        $this->environment->resetAnonymousStack();

        if (preg_match_all('/(_*)(([a-z0-9]+)|(`(.+)`))__/mUsi', $span, $matches) > 0) {
            foreach ($matches[3] as $k => $y) {
                $name = $matches[3][$k] ? $matches[3][$k] : $matches[5][$k];

                // string prefixed with _ is not an anonymous link
                if ($matches[1][$k]) {
                    continue;
                }

                $this->environment->pushAnonymous($name);
            }
        }

        // Replacing anonymous links  `here <http://google.com>`__
        $span = (string) preg_replace_callback(
            '/(^|[ \(])(([a-z0-9_-]+)|(`(.+)`))__([^a-z0-9]{1}|$)/mUsi',
            [$this, 'registerAnonymousLinkFromMatches'],
            $span
        );

        // Replacing other links  `Google <http://google.com>`_
        $span = (string) preg_replace_callback(
            '/(^|[ \(])(([a-z0-9_-]+)|(`(.+)`))_([^a-z0-9_]{1}|$)/mUsi',
            [$this, 'registerLinkFromMatches'],
            $span
        );

        return $span;
    }

    /** @param string[] $match */
    private function registerAnonymousLinkFromMatches(array $match): string
    {
        return $this->registerLinkFromMatches($match, true);
    }

    /** @param string[] $match */
    private function registerLinkFromMatches(array $match, bool $anonymous = false): string
    {
        $link = $match[3] !== '' ? $match[3] : $match[5];

        // a link starting with _ is not a link - return original string
        if (substr($link, 0, 1) === '_') {
            return $match[0];
        }

        // the link may have a new line in it so we need to strip it
        // before setting the link and adding a token to be replaced
        $link = str_replace("\n", ' ', $link);
        $link = (string) preg_replace('/\s+/', ' ', $link);

        // we need to maintain the characters before and after the link
        $prev = $match[1]; // previous character before the link
        $next = $match[6]; // next character after the link

        $url = '';

        // extract the url if the link was in this format: `test link <https://www.google.com>`_
        if (preg_match('/^(.+)[ \n]<(.+)>$/mUsi', $link, $m) > 0) {
            $link = $m[1];
            $url  = $m[2];
            if (! $anonymous) {
                $this->environment->setLinkTarget(new LinkTarget($link, $url));
            }
        }

        // extract the url if the link was in this format: `<https://www.google.com>`_
        if (preg_match('/^<(.+)>$/mUsi', $link, $m) > 0) {
            $link = $m[1];
            $url  = $m[1];
            if (! $anonymous) {
                $this->environment->setLinkTarget(new LinkTarget($link, $url));
            }
        }

        $id = $this->generateId();

        $this->addToken(SpanToken::TYPE_LINK, $id, [
            'link' => $link,
            'url' => $url,
        ]);

        return $prev . $id . $next;
    }

    private function replaceStandaloneHyperlinks(string $span): string
    {
        // Replace standalone hyperlinks using a modified version of @gruber's
        // "Liberal Regex Pattern for all URLs", https://gist.github.com/gruber/249502
        $absoluteUriPattern = '#(?i)\b((?:[a-z][\w\-+.]+:(?:/{1,3}|[a-z0-9%]))('
            . '?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>'
            . ']+|(\([^\s()<>]+\)))*\)|[^\s\`!()\[\]{};:\'".,<>?«»“”‘’]))#';

        // Standalone hyperlink callback
        $standaloneHyperlinkCallback = function ($match, $scheme = ''): string {
            $id  = $this->generateId();
            $url = $match[1];

            $this->addToken(SpanToken::TYPE_LINK, $id, [
                'link' => $url,
                'url' => $scheme . $url,
            ]);

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
        $emailAddressPattern = '/((?:[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9'
            . '!#$%&\'*+\/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x'
            . '23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z'
            . '0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|'
            . '\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2'
            . '[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0'
            . 'b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f'
            . '])+)\]))/msi';

        $standaloneEmailAddressCallback = function (array $match): string {
            $id  = $this->generateId();
            $url = $match[1];

            $this->addToken(SpanToken::TYPE_LINK, $id, [
                'link' => $url,
                'url' => 'mailto:' . $url,
            ]);

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

    private function generateId(): string
    {
        $this->tokenId++;

        return sha1($this->prefix . '|' . $this->tokenId);
    }
}
