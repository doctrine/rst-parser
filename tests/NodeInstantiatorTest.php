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
        $environment = $this->createMock(Environment::class);

        $nodeInstantiator = new NodeInstantiator(NodeTypes::DOCUMENT, DocumentNode::class, $environment);

        self::assertSame(NodeTypes::DOCUMENT, $nodeInstantiator->getType());
    }

    public function testInvalidTypeThrowsInvalidArgumentException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Node type invalid is not a valid node type.');

        $environment = $this->createMock(Environment::class);

        $nodeInstantiator = new NodeInstantiator('invalid', DocumentNode::class, $environment);
    }

    public function testCreate() : void
    {
        $environment = $this->createMock(Environment::class);

        $nodeInstantiator = new NodeInstantiator(NodeTypes::DOCUMENT, DocumentNode::class, $environment);

        $document = $nodeInstantiator->create([$environment]);

        self::assertInstanceOf(DocumentNode::class, $document);
        self::assertInstanceOf(Environment::class, $document->getEnvironment());
    }
}
