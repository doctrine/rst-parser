<?php

declare(strict_types=1);

namespace Doctrine\RST\Renderers;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\SpanNode;
use Doctrine\RST\References\ResolvedReference;
use Doctrine\RST\Span\SpanToken;

use function is_string;
use function preg_replace;
use function preg_replace_callback;
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
        return (string) preg_replace_callback('/\*\*(.+)\*\*/mUsi', fn (array $matches): string => $this->strongEmphasis($matches[1]), $span);
    }

    private function renderEmphasis(string $span): string
    {
        return (string) preg_replace_callback('/\*(.+)\*/mUsi', fn (array $matches): string => $this->emphasis($matches[1]), $span);
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
        if ($spanToken->getType() === SpanToken::TYPE_LINK) {
            $spanToken->set('linktext', $this->renderSyntaxes($spanToken->get('link')));
        }

        $textRole = $spanToken->getTextRole();

        if ($textRole === null) {
            return $spanToken->get('text');
        }

        $resolvedTextRole = $textRole->render($this->environment, $spanToken);

        return str_replace($spanToken->getId(), $resolvedTextRole, $span);
    }

    /** @param mixed[] $attributes */
    public function link(?string $url, string $title, array $attributes = []): string
    {
        return $this->environment->getLinkRenderer()->renderUrl($url, $title, $attributes);
    }

    /** @param mixed[] $value */
    public function reference(ResolvedReference $reference, array $value): string
    {
        return $this->environment->getLinkRenderer()->renderReference($reference, $value);
    }
}
