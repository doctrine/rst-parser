<?php

declare(strict_types=1);

namespace Doctrine\RST\TextRoles;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Span\SpanProcessor;
use Doctrine\RST\Span\SpanToken;

use function preg_replace_callback;

class VariableTextRole extends SpecialTextRole
{
    public function __construct()
    {
        parent::__construct('variable');
    }

    public function replaceAndRegisterTokens(SpanProcessor $spanProcessor, string $span): string
    {
        return (string) preg_replace_callback(
            '/\|(.+)\|/mUsi',
            function (array $match) use ($spanProcessor): string {
                $id = $spanProcessor->generateId();
                $spanProcessor->addToken(
                    new SpanToken($this, $id, [
                        'type' => $this->getName(),
                        'variable' => $match[1],
                    ])
                );

                return $id;
            },
            $span
        );
    }

    public function hasRecursiveSyntax(): bool
    {
        return true;
    }

    public function render(Environment $environment, SpanToken $spanToken): string
    {
        $variable = $environment->getVariable($spanToken->get('variable'));

        if ($variable === null) {
            return '';
        }

        if ($variable instanceof Node) {
            return $variable->render();
        }

        return (string) $variable;
    }
}
