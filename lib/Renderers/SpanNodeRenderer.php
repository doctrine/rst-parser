<?php

declare(strict_types=1);

namespace Doctrine\RST\Renderers;

use Doctrine\RST\Environment;
use Doctrine\RST\InvalidLink;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\SpanNode;
use Doctrine\RST\Span\SpanToken;
use InvalidArgumentException;

use function is_string;
use function preg_replace;
use function preg_replace_callback;
use function sprintf;
use function str_replace;

abstract class SpanNodeRenderer implements NodeRenderer, SpanRenderer
{
    /** @var Environment */
    protected $environment;

    /** @var SpanNode */
    protected $span;

    public function __construct(
        Environment $environment,
        SpanNode $span
    ) {
        $this->environment = $environment;
        $this->span        = $span;
    }

    public function render(): string
    {
        $value = $this->span->getValue();

        $span = $this->renderSyntaxes($value);

        $span = $this->renderTokens($span);

        return $span;
    }

    private function renderSyntaxes(string $span): string
    {
        $span = $this->escape($span);

        $span = $this->renderStrongEmphasis($span);

        $span = $this->renderEmphasis($span);

        $span = $this->renderNbsp($span);

        $span = $this->renderVariables($span);

        $span = $this->renderBrs($span);

        return $span;
    }

    private function renderStrongEmphasis(string $span): string
    {
        return (string) preg_replace_callback('/\*\*(.+)\*\*/mUsi', function (array $matches): string {
            return $this->strongEmphasis($matches[1]);
        }, $span);
    }

    private function renderEmphasis(string $span): string
    {
        return (string) preg_replace_callback('/\*(.+)\*/mUsi', function (array $matches): string {
            return $this->emphasis($matches[1]);
        }, $span);
    }

    private function renderNbsp(string $span): string
    {
        return (string) preg_replace('/~/', $this->nbsp(), $span);
    }

    private function renderVariables(string $span): string
    {
        return (string) preg_replace_callback('/\|(.+)\|/mUsi', function (array $match): string {
            $variable = $this->environment->getVariable($match[1]);

            if ($variable === null) {
                return '';
            }

            if ($variable instanceof Node) {
                return $variable->render();
            }

            if (is_string($variable)) {
                return $variable;
            }

            return (string) $variable;
        }, $span);
    }

    private function renderBrs(string $span): string
    {
        // Adding brs when a space is at the end of a line
        return (string) preg_replace('/ \n/', $this->br(), $span);
    }

    private function renderTokens(string $span): string
    {
        foreach ($this->span->getTokens() as $token) {
            $span = $this->renderToken($token, $span);
        }

        return $span;
    }

    private function renderToken(SpanToken $spanToken, string $span): string
    {
        switch ($spanToken->getType()) {
            case SpanToken::TYPE_LITERAL:
                return $this->renderLiteral($spanToken, $span);

            case SpanToken::TYPE_TEXT_ROLE:
                return $this->renderTextNode($spanToken, $span);

            case SpanToken::TYPE_LINK:
                return $this->renderLink($spanToken, $span);
        }

        throw new InvalidArgumentException(sprintf('Unknown token type %s', $spanToken->getType()));
    }

    private function renderLiteral(SpanToken $spanToken, string $span): string
    {
        return str_replace(
            $spanToken->getId(),
            $this->literal($spanToken->get('text')),
            $span
        );
    }

    private function renderTextNode(SpanToken $spanToken, string $span): string
    {
        if ($this->environment->isReference($spanToken->get('section'))) {
            return $this->renderReference($spanToken, $span);
        }

        $textRole = $this->environment->getTextRole($spanToken->get('section'));

        if ($textRole === null) {
            return $spanToken->get('url');
        }

        $resolvedTextRole = $textRole->process($spanToken->get('url'));

        return str_replace($spanToken->getId(), $resolvedTextRole, $span);
    }

    private function renderReference(SpanToken $spanToken, string $span): string
    {
        $reference = $this->environment->resolve($spanToken->get('section'), $spanToken->get('url'));

        if ($reference === null) {
            $this->environment->addInvalidLink(new InvalidLink($spanToken->get('url')));

            return str_replace($spanToken->getId(), $spanToken->get('text'), $span);
        }

        $link = $this->reference($reference, $spanToken->getTokenData());

        return str_replace($spanToken->getId(), $link, $span);
    }

    private function renderLink(SpanToken $spanToken, string $span): string
    {
        $url  = $spanToken->get('url');
        $link = $spanToken->get('link');

        if ($url === '') {
            $linkTarget = $this->environment->getLinkTarget($link);
            if ($linkTarget !== null) {
                $url = $linkTarget->getUrl();
            }

            if ($url === '') {
                $metaEntry = $this->environment->getMetaEntry();

                if ($metaEntry !== null && $metaEntry->hasTitle($link)) {
                    // A strangely-complex way to simply get the current relative URL
                    // For example, if the current page is "reference/page", then
                    // this would return "page" so the final URL is href="page#some-anchor".
                    $currentRelativeUrl = $this->environment->relativeUrl('/' . $metaEntry->getUrl());
                    $url                = $currentRelativeUrl . '#' . Environment::slugify($link);
                }
            }

            if ($url === '') {
                $this->environment->addInvalidLink(new InvalidLink($link));

                return str_replace($spanToken->getId(), $link, $span);
            }
        }

        $link = $this->link($url, $this->renderSyntaxes($link));

        return str_replace($spanToken->getId(), $link, $span);
    }
}
