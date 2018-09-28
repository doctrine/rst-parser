<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Environment;
use Doctrine\RST\HTML\Document;
use Doctrine\RST\NodeInstantiator;
use Doctrine\RST\NodeTypes;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class NodeInstantiatorTest extends TestCase
{
    public function testGetType() : void
    {
        $nodeInstantiator = new NodeInstantiator(NodeTypes::DOCUMENT, Document::class);

        self::assertSame(NodeTypes::DOCUMENT, $nodeInstantiator->getType());
    }

    public function testInvalidTypeThrowsInvalidArgumentException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Node type invalid is not a valid node type.');

        $nodeInstantiator = new NodeInstantiator('invalid', Document::class);
    }

    public function testCreate() : void
    {
        $nodeInstantiator = new NodeInstantiator(NodeTypes::DOCUMENT, Document::class);

        $environment = $this->createMock(Environment::class);

        $document = $nodeInstantiator->create([$environment]);

        self::assertInstanceOf(Document::class, $document);
    }
}
