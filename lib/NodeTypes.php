<?php

declare(strict_types=1);

namespace Doctrine\RST;

final class NodeTypes
{
    public const DOCUMENT  = 'document';
    public const TOC       = 'toc';
    public const TITLE     = 'title';
    public const SEPARATOR = 'separator';
    public const CODE      = 'code';
    public const QUOTE     = 'quote';
    public const PARAGRAPH = 'paragraph';
    public const ANCHOR    = 'anchor';
    public const LIST      = 'list';
    public const TABLE     = 'table';
    public const SPAN      = 'span';

    public const NODES = [
        self::DOCUMENT,
        self::TOC,
        self::TITLE,
        self::SEPARATOR,
        self::CODE,
        self::QUOTE,
        self::PARAGRAPH,
        self::ANCHOR,
        self::LIST,
        self::TABLE,
        self::SPAN,
    ];

    private function __construct()
    {
    }
}
