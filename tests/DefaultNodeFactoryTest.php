<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\DefaultNodeFactory;
use Doctrine\RST\Environment;
use Doctrine\RST\HTML\Document;
use Doctrine\RST\HTML\Nodes\AnchorNode;
use Doctrine\RST\HTML\Nodes\CodeNode;
use Doctrine\RST\HTML\Nodes\ListNode;
use Doctrine\RST\HTML\Nodes\ParagraphNode;
use Doctrine\RST\HTML\Nodes\QuoteNode;
use Doctrine\RST\HTML\Nodes\SeparatorNode;
use Doctrine\RST\HTML\Nodes\TableNode;
use Doctrine\RST\HTML\Nodes\TitleNode;
use Doctrine\RST\HTML\Nodes\TocNode;
use Doctrine\RST\HTML\Span;
use Doctrine\RST\NodeInstantiator;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\NodeTypes;
use Doctrine\RST\Parser;
use Doctrine\RST\Parser\LineChecker;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DefaultNodeFactoryTest extends TestCase
{
    public function testCreateDocument() : void
    {
        $returnClass = Document::class;
        $type        = NodeTypes::DOCUMENT;

        $environment      = $this->createMock(Environment::class);
        $nodeInstantiator = $this->createMock(NodeInstantiator::class);
        $expectedReturn   = $this->createMock($returnClass);

        $nodeInstantiator->expects(self::once())
            ->method('getType')
            ->willReturn($type);

        $nodeInstantiator->expects(self::once())
            ->method('create')
            ->with([$environment])
            ->willReturn($expectedReturn);

        $defaultNodeFactory = new DefaultNodeFactory($nodeInstantiator);

        self::assertSame($expectedReturn, $defaultNodeFactory->createDocument($environment));
    }

    public function testCreateToc() : void
    {
        $returnClass = TocNode::class;
        $type        = NodeTypes::TOC;

        $environment      = $this->createMock(Environment::class);
        $nodeInstantiator = $this->createMock(NodeInstantiator::class);
        $expectedReturn   = $this->createMock($returnClass);

        $nodeInstantiator->expects(self::once())
            ->method('getType')
            ->willReturn($type);

        $nodeInstantiator->expects(self::once())
            ->method('create')
            ->with([$environment, [], []])
            ->willReturn($expectedReturn);

        $defaultNodeFactory = new DefaultNodeFactory($nodeInstantiator);

        self::assertSame($expectedReturn, $defaultNodeFactory->createToc($environment, [], []));
    }

    public function testCreateTitle() : void
    {
        $returnClass = TitleNode::class;
        $type        = NodeTypes::TITLE;

        $node             = $this->createMock(Node::class);
        $nodeInstantiator = $this->createMock(NodeInstantiator::class);
        $expectedReturn   = $this->createMock($returnClass);

        $nodeInstantiator->expects(self::once())
            ->method('getType')
            ->willReturn($type);

        $nodeInstantiator->expects(self::once())
            ->method('create')
            ->with([$node, 1, 'test'])
            ->willReturn($expectedReturn);

        $defaultNodeFactory = new DefaultNodeFactory($nodeInstantiator);

        self::assertSame($expectedReturn, $defaultNodeFactory->createTitle($node, 1, 'test'));
    }

    public function testCreateSeparator() : void
    {
        $returnClass = SeparatorNode::class;
        $type        = NodeTypes::SEPARATOR;

        $nodeInstantiator = $this->createMock(NodeInstantiator::class);
        $expectedReturn   = $this->createMock($returnClass);

        $nodeInstantiator->expects(self::once())
            ->method('getType')
            ->willReturn($type);

        $nodeInstantiator->expects(self::once())
            ->method('create')
            ->with([1])
            ->willReturn($expectedReturn);

        $defaultNodeFactory = new DefaultNodeFactory($nodeInstantiator);

        self::assertSame($expectedReturn, $defaultNodeFactory->createSeparator(1));
    }

    public function testCreateCode() : void
    {
        $returnClass = CodeNode::class;
        $type        = NodeTypes::CODE;

        $nodeInstantiator = $this->createMock(NodeInstantiator::class);
        $expectedReturn   = $this->createMock($returnClass);

        $nodeInstantiator->expects(self::once())
            ->method('getType')
            ->willReturn($type);

        $nodeInstantiator->expects(self::once())
            ->method('create')
            ->with([[]])
            ->willReturn($expectedReturn);

        $defaultNodeFactory = new DefaultNodeFactory($nodeInstantiator);

        self::assertSame($expectedReturn, $defaultNodeFactory->createCode([]));
    }

    public function testCreateQuote() : void
    {
        $returnClass = QuoteNode::class;
        $type        = NodeTypes::QUOTE;

        $nodeInstantiator = $this->createMock(NodeInstantiator::class);
        $expectedReturn   = $this->createMock($returnClass);

        $nodeInstantiator->expects(self::once())
            ->method('getType')
            ->willReturn($type);

        $nodeInstantiator->expects(self::once())
            ->method('create')
            ->with([[]])
            ->willReturn($expectedReturn);

        $defaultNodeFactory = new DefaultNodeFactory($nodeInstantiator);

        self::assertSame($expectedReturn, $defaultNodeFactory->createQuote([]));
    }

    public function testCreateParagraph() : void
    {
        $returnClass = ParagraphNode::class;
        $type        = NodeTypes::PARAGRAPH;

        $nodeInstantiator = $this->createMock(NodeInstantiator::class);
        $expectedReturn   = $this->createMock($returnClass);

        $nodeInstantiator->expects(self::once())
            ->method('getType')
            ->willReturn($type);

        $nodeInstantiator->expects(self::once())
            ->method('create')
            ->with(['test'])
            ->willReturn($expectedReturn);

        $defaultNodeFactory = new DefaultNodeFactory($nodeInstantiator);

        self::assertSame($expectedReturn, $defaultNodeFactory->createParagraph('test'));
    }

    public function testCreateAnchor() : void
    {
        $returnClass = AnchorNode::class;
        $type        = NodeTypes::ANCHOR;

        $nodeInstantiator = $this->createMock(NodeInstantiator::class);
        $expectedReturn   = $this->createMock($returnClass);

        $nodeInstantiator->expects(self::once())
            ->method('getType')
            ->willReturn($type);

        $nodeInstantiator->expects(self::once())
            ->method('create')
            ->with(['test'])
            ->willReturn($expectedReturn);

        $defaultNodeFactory = new DefaultNodeFactory($nodeInstantiator);

        self::assertSame($expectedReturn, $defaultNodeFactory->createAnchor('test'));
    }

    public function testCreateList() : void
    {
        $returnClass = ListNode::class;
        $type        = NodeTypes::LIST;

        $nodeInstantiator = $this->createMock(NodeInstantiator::class);
        $expectedReturn   = $this->createMock($returnClass);

        $nodeInstantiator->expects(self::once())
            ->method('getType')
            ->willReturn($type);

        $nodeInstantiator->expects(self::once())
            ->method('create')
            ->with(['test'])
            ->willReturn($expectedReturn);

        $defaultNodeFactory = new DefaultNodeFactory($nodeInstantiator);

        self::assertSame($expectedReturn, $defaultNodeFactory->createList('test'));
    }

    public function testCreateTable() : void
    {
        $returnClass = TableNode::class;
        $type        = NodeTypes::TABLE;

        $lineChecker      = $this->createMock(LineChecker::class);
        $nodeInstantiator = $this->createMock(NodeInstantiator::class);
        $expectedReturn   = $this->createMock($returnClass);

        $nodeInstantiator->expects(self::once())
            ->method('getType')
            ->willReturn($type);

        $nodeInstantiator->expects(self::once())
            ->method('create')
            ->with([[], TableNode::TYPE_SIMPLE, $lineChecker])
            ->willReturn($expectedReturn);

        $defaultNodeFactory = new DefaultNodeFactory($nodeInstantiator);

        self::assertSame($expectedReturn, $defaultNodeFactory->createTable([], TableNode::TYPE_SIMPLE, $lineChecker));
    }

    public function testCreateSpan() : void
    {
        $returnClass = Span::class;
        $type        = NodeTypes::SPAN;

        $parser           = $this->createMock(Parser::class);
        $nodeInstantiator = $this->createMock(NodeInstantiator::class);
        $expectedReturn   = $this->createMock($returnClass);

        $nodeInstantiator->expects(self::once())
            ->method('getType')
            ->willReturn($type);

        $nodeInstantiator->expects(self::once())
            ->method('create')
            ->with([$parser, 'test'])
            ->willReturn($expectedReturn);

        $defaultNodeFactory = new DefaultNodeFactory($nodeInstantiator);

        self::assertSame($expectedReturn, $defaultNodeFactory->createSpan($parser, 'test'));
    }

    public function testGetNodeInstantiatorThrowsInvalidArgumentException() : void
    {
        $nodeInstantiator = $this->createMock(NodeInstantiator::class);

        $nodeInstantiator->expects(self::once())
            ->method('getType')
            ->willReturn('invalid');

        $defaultNodeFactory = new DefaultNodeFactory($nodeInstantiator);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not find node instantiator of type list');

        $defaultNodeFactory->createList('test');
    }
}
