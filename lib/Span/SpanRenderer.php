<?php

declare(strict_types=1);

namespace Doctrine\RST\Span;

use Doctrine\RST\Environment;
use Doctrine\RST\Span;
use InvalidArgumentException;
use function preg_replace;
use function preg_replace_callback;
use function sprintf;
use function str_replace;

class SpanRenderer
{
    /** @var Environment */
    private $environment;

    /** @var Span */
    private $span;

    /** @var string */
    private $spanString;

    /** @var SpanToken[] */
    private $tokens;

    /**
     * @param SpanToken[] $tokens
     */
    public function __construct(
        Environment $environment,
        Span $span,
        string $spanString,
        array $tokens
    ) {
        $this->environment = $environment;
        $this->span        = $span;
        $this->spanString  = $spanString;
        $this->tokens      = $tokens;
    }

    public function render() : string
    {
        $span = $this->renderSyntaxes($this->spanString);

        $span = $this->renderTokens($span);

        return $span;
    }

    private function renderSyntaxes(string $span) : string
    {
        $span = $this->span->escape($span);

        $span = $this->renderStrongEmphasis($span);

        $span = $this->renderEmphasis($span);

        $span = $this->renderNbsp($span);

        $span = $this->renderVariables($span);

        $span = $this->renderBrs($span);

        return $span;
    }

    private function renderStrongEmphasis(string $span) : string
    {
        return preg_replace_callback('/\*\*(.+)\*\*/mUsi', function (array $matches) : string {
            return $this->span->strongEmphasis($matches[1]);
        }, $span);
    }

    private function renderEmphasis(string $span) : string
    {
        return preg_replace_callback('/\*(.+)\*/mUsi', function (array $matches) : string {
            return $this->span->emphasis($matches[1]);
        }, $span);
    }

    private function renderNbsp(string $span) : string
    {
        return preg_replace('/~/', $this->span->nbsp(), $span);
    }

    private function renderVariables(string $span) : string
    {
        return preg_replace_callback('/\|(.+)\|/mUsi', function (array $match) {
            return $this->environment->getVariable($match[1]);
        }, $span);
    }

    private function renderBrs(string $span) : string
    {
        // Adding brs when a space is at the end of a line
        return preg_replace('/ \n/', $this->span->br(), $span);
    }

    private function renderTokens(string $span) : string
    {
        foreach ($this->tokens as $token) {
            $span = $this->renderToken($token, $span);
        }

        return $span;
    }

    private function renderToken(SpanToken $spanToken, string $span) : string
    {
        switch ($spanToken->getType()) {
            case SpanToken::TYPE_LITERAL:
                return $this->renderLiteral($spanToken, $span);

            case SpanToken::TYPE_REFERENCE:
                return $this->renderReference($spanToken, $span);

            case SpanToken::TYPE_LINK:
                return $this->renderLink($spanToken, $span);
        }

        throw new InvalidArgumentException(sprintf('Unknown token type %s', $spanToken->getType()));
    }

    private function renderLiteral(SpanToken $spanToken, string $span) : string
    {
        return str_replace(
            $spanToken->getId(),
            $this->span->literal($spanToken->get('text')),
            $span
        );
    }

    private function renderReference(SpanToken $spanToken, string $span) : string
    {
        $reference = $this->environment->resolve($spanToken->get('section'), $spanToken->get('url'));

        $link = $this->span->reference($reference, $spanToken->getTokenData());

        return str_replace($spanToken->getId(), $link, $span);
    }

    private function renderLink(SpanToken $spanToken, string $span) : string
    {
        if ($spanToken->get('url') !== '') {
            $url = $spanToken->get('url');
        } elseif ($spanToken->get('anchor') !== '') {
            $link = $this->environment->getLink($spanToken->get('link'));

            if ($link !== '') {
                $url = $link;
            } else {
                $url = '#' . $spanToken->get('anchor');
            }
        } else {
            $url = $this->environment->getLink($spanToken->get('link'));
        }

        $link = $this->span->link($url, $this->renderSyntaxes($spanToken->get('link')));

        return str_replace($spanToken->getId(), $link, $span);
    }
}
