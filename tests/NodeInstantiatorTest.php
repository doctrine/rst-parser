<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Environment;
use Doctrine\RST\NodeFactory\NodeInstantiator;
use Doctrine\RST\Nodes\DocumentNode;
use Doctrine\RST\Nodes\NodeTypes;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class NodeInstantiatorTest extends TestCase
{
    public function testGetType() : void
    {
        $nodeInstantiator = new NodeInstantiator(NodeTypes::DOCUMENT, DocumentNode::class);

        self::assertSame(NodeTypes::DOCUMENT, $nodeInstantiator->getType());
    }

    public function testInvalidTypeThrowsInvalidArgumentException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Node type invalid is not a valid node type.');

        $nodeInstantiator = new NodeInstantiator('invalid', DocumentNode::class);
    }

    public function testCreate() : void
    {
        $nodeInstantiator = new NodeInstantiator(NodeTypes::DOCUMENT, DocumentNode::class);

        $environment = $this->createMock(Environment::class);

        $document = $nodeInstantiator->create([$environment]);

        self::assertInstanceOf(DocumentNode::class, $document);
    }
}
