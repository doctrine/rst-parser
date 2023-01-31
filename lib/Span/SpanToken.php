<?php

declare(strict_types=1);

namespace Doctrine\RST\Span;

final class SpanToken
{
    public const TYPE_LITERAL     = 'literal';
    public const TYPE_INTERPRETED = 'interpreted';
    public const TYPE_TEXT_ROLE   = 'textrole';
    public const TYPE_LINK        = 'link';

    private string $type;

    private string $id;

    /** @var string[] */
    private array $token;

    /** @param string[] $token */
    public function __construct(string $type, string $id, array $token)
    {
        $this->type          = $type;
        $this->id            = $id;
        $this->token         = $token;
        $this->token['type'] = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function get(string $key): string
    {
        return $this->token[$key] ?? '';
    }

    /** @return string[] */
    public function getTokenData(): array
    {
        return $this->token;
    }
}
