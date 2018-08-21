<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Document;
use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Nodes\DummyNode;
use Doctrine\RST\Nodes\ListNode;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\ParagraphNode;
use Doctrine\RST\Nodes\QuoteNode;
use Doctrine\RST\Nodes\TableNode;
use Doctrine\RST\Nodes\TitleNode;
use Doctrine\RST\Parser;
use PHPUnit\Framework\TestCase;
use function count;
use function file_get_contents;
use function sprintf;
use function trim;

/**
 * Unit testing for RST
 */
class ParserTest extends TestCase
{
    /**
     * Tests that comments are not present in the rendered document
     */
    public function testComments() : void
    {
        $document = $this->parse('comment.rst');

        $render = $document->render();
        $this->assertNotContains('Testing comment', $render);
        $this->assertContains('Text before', $render);
        $this->assertContains('Text after', $render);

        $document = $this->parse('multi-comment.rst');

        $render = $document->render();
        $this->assertNotContains('multi-line', $render);
        $this->assertNotContains('Blha', $render);
        $this->assertContains('Text before', $render);
        $this->assertContains('Text after', $render);
    }

    /**
     * Testing raw node
     */
    public function testRawNode() : void
    {
        $document = $this->parse('empty.rst');
        $document->addNode('hello');

        $this->assertContains('hello', $document->render());
    }

    /**
     * Testing that code node value is good
     */
    public function testCodeNode() : void
    {
        $document = $this->parse('code-block-lastline.rst');

        $nodes = $document->getNodes(function ($node) {
            return $node instanceof CodeNode;
        });

        $this->assertEquals(1, count($nodes));
        $this->assertEquals("A\nB\n C", trim($nodes[0]->getValue()));
    }

    /**
     * Testing paragraph nodes
     */
    public function testParagraphNode() : void
    {
        $document = $this->parse('paragraph.rst');

        $this->assertHasNode($document, function ($node) {
            return $node instanceof ParagraphNode;
        }, 1);
        $this->assertContains('Hello world!', $document->render());
    }

    /**
     * Testing multi-paragraph nodes
     */
    public function testParagraphNodes() : void
    {
        $document = $this->parse('paragraphs.rst');

        $this->assertHasNode($document, function ($node) {
            return $node instanceof ParagraphNode;
        }, 3);
    }

    /**
     * Testing quote and block code
     */
    public function testBlockNode() : void
    {
        $quote = $this->parse('quote.rst');

        $this->assertHasNode($quote, function ($node) {
            return $node instanceof QuoteNode;
        }, 1);

        $code = $this->parse('code.rst');

        $this->assertHasNode($quote, function ($node) {
            return $node instanceof QuoteNode;
        }, 1);

        $this->assertNotContains('::', $code->render());
    }

    /**
     * Testing the titling
     */
    public function testTitles() : void
    {
        $document = $this->parse('title.rst');

        $this->assertHasNode($document, function ($node) {
            return $node instanceof TitleNode
                && $node->getLevel() === 1;
        }, 1);

        $document = $this->parse('title2.rst');

        $this->assertHasNode($document, function ($node) {
            return $node instanceof TitleNode
                && $node->getLevel() === 2;
        }, 1);
    }

    /**
     * Testing the titling
     */
    public function testList() : void
    {
        $document = $this->parse('list.rst');

        $this->assertHasNode($document, function ($node) {
            return $node instanceof ListNode;
        }, 1);

        $document = $this->parse('indented-list.rst');

        $this->assertHasNode($document, function ($node) {
            return $node instanceof ListNode;
        }, 1);

        $document = $this->parse('list-empty.rst');
        $this->assertHasNode($document, function ($node) {
            return $node instanceof ListNode;
        }, 1);
    }

    /**
     * Testing the titles retrieving
     */
    public function testGetTitles() : void
    {
        $document = $this->parse('titles.rst');

        $this->assertEquals($document->getTitle(), 'The main title');
        $this->assertEquals($document->getTitles(), [
            [
        'The main title',
        [
                [
        'First level title',
        [
                    ['Second level title', []],
                    ['Other second level title', []],
                ],
                ],
                [
                'Other first level title',
                [
                    ['Next second level title', []],
                    ['Yet another second level title', []],
                ],
                ],
            ],
            ],
        ]);
    }

    /**
     * Testing the table feature
     */
    public function testTable() : void
    {
        $document = $this->parse('table.rst');

        $nodes = $document->getNodes(function ($node) {
            return $node instanceof TableNode;
        });

        $this->assertEquals(count($nodes), 1);

        if ($nodes) {
            $table = $nodes[0];
            $this->assertEquals(3, $table->getCols());
            $this->assertEquals(3, $table->getRows());
        }

        $document = $this->parse('pretty-table.rst');

        $nodes = $document->getNodes(function ($node) {
            return $node instanceof TableNode;
        });

        $this->assertEquals(count($nodes), 1);

        if (! $nodes) {
            return;
        }

        $table = $nodes[0];
        $this->assertEquals(3, $table->getCols());
        $this->assertEquals(2, $table->getRows());
    }

    /**
     * Tests that a simple replace works
     */
    public function testReplace() : void
    {
        $document = $this->parse('replace.rst');

        $this->assertContains('Hello world!', $document->render());
    }

    /**
     * Test the include:: pseudo-directive
     */
    public function testInclusion() : void
    {
        $document = $this->parse('inclusion.rst');

        $this->assertContains('I was actually included', $document->renderDocument());
    }

    public function testDirective() : void
    {
        $document = $this->parse('directive.rst');

        $nodes = $document->getNodes(function ($node) {
            return $node instanceof DummyNode;
        });

        $this->assertEquals(1, count($nodes));

        if (! $nodes) {
            return;
        }

        $node = $nodes[0];
        $data = $node->data;
        $this->assertEquals('some data', $data['data']);
        $options = $data['options'];
        $this->assertTrue(isset($options['maxdepth']));
        $this->assertTrue(isset($options['titlesonly']));
        $this->assertTrue(isset($options['glob']));
        $this->assertTrue($options['titlesonly']);
        $this->assertEquals(123, $options['maxdepth']);
    }

    public function testSubsequentParsesDontHaveTheSameTitleLevelOrder() : void
    {
        $directory = __DIR__ . '/files';

        $parser = new Parser();
        $parser->getEnvironment()->setCurrentDirectory($directory);

        /** @var TitleNode[] $nodes1 */
        /** @var TitleNode[] $nodes2 */
        $nodes1 = $parser->parseFile(sprintf('%s/mixed-titles-1.rst', $directory))->getNodes();
        $nodes2 = $parser->parseFile(sprintf('%s/mixed-titles-2.rst', $directory))->getNodes();

        $this->assertSame(1, $nodes1[0]->getLevel());
        $this->assertSame(2, $nodes1[1]->getLevel());
        $this->assertSame(1, $nodes2[0]->getLevel(), 'Title level in second parse is influenced by first parse');
        $this->assertSame(2, $nodes2[1]->getLevel(), 'Title level in second parse is influenced by first parse');
    }

    public function testNewlineBeforeAnIncludedIsntGobbled() : void
    {
        /** @var Node[] $nodes */
        $nodes = $this->parse('inclusion-newline.rst')->getNodes();

        $this->assertCount(3, $nodes);
        $this->assertInstanceOf('Doctrine\RST\Nodes\TitleNode', $nodes[0]);
        $this->assertInstanceOf('Doctrine\RST\Nodes\ParagraphNode', $nodes[1]);
        $this->assertInstanceOf('Doctrine\RST\Nodes\ParagraphNode', $nodes[2]);
        $this->assertContains('<p>Test this paragraph is present.</p>', $nodes[1]->render());
        $this->assertContains('<p>And this one as well.</p>', $nodes[2]->render());
    }

    public function testIncludesKeepScope() : void
    {
        // See http://docutils.sourceforge.net/docs/ref/rst/directives.html#including-an-external-document-fragment

        /** @var Node[] $nodes */
        $nodes = $this->parse('inclusion-scope.rst')->getNodes();

        $this->assertCount(4, $nodes);
        $this->assertEquals("This first example will be parsed at the document level, and can\nthus contain any construct, including section headers.", $nodes[0]->getValue()->render());
        $this->assertEquals('This is included.', $nodes[1]->getValue()->render());
        $this->assertEquals('Back in the main document.', $nodes[2]->getValue()->render());
        $this->assertInstanceOf('Doctrine\RST\Nodes\QuoteNode', $nodes[3]);
        $this->assertContains('This is included.', $nodes[3]->getValue()->render());
    }

    public function testIncludesPolicy() : void
    {
        $directory   = __DIR__ . '/files/';
        $parser      = new Parser();
        $environment = $parser->getEnvironment();
        $environment->setCurrentDirectory($directory);

        // Test defaults
        $this->assertTrue($parser->getIncludeAllowed());
        $this->assertSame('', $parser->getIncludeRoot());

        // Default policy:
        $document = (string) $parser->parseFile($directory . 'inclusion-policy.rst');
        $this->assertContains('SUBDIRECTORY OK', $document);
        $this->assertContains('EXTERNAL FILE INCLUDED!', $document);

        // Disbaled policy:
        $parser->setIncludePolicy(false);
        $nodes = $parser->parseFile($directory . 'inclusion-policy.rst')->getNodes();
        $this->assertCount(1, $nodes);

        // Enabled
        $parser->setIncludePolicy(true);
        $nodes = $parser->parseFile($directory . 'inclusion-policy.rst')->getNodes();
        $this->assertCount(6, $nodes);

        // Jailed
        $parser->setIncludePolicy(true, $directory);
        $nodes = $parser->parseFile($directory . 'inclusion-policy.rst')->getNodes();
        $this->assertCount(5, $nodes);
    }

    /**
     * Helper function, parses a file and returns the document
     * produced by the parser
     */
    private function parse(string $file) : Document
    {
        $directory   = __DIR__ . '/files/';
        $parser      = new Parser();
        $environment = $parser->getEnvironment();
        $environment->setCurrentDirectory($directory);

        $data = file_get_contents($directory . $file);

        return $parser->parse($data);
    }

    /**
     * Asserts that a document has nodes that satisfy the function
     */
    private function assertHasNode(Document $document, callable $function, ?int $count = null) : void
    {
        $nodes = $document->getNodes($function);
        $this->assertNotEmpty($nodes);

        if ($count === null) {
            return;
        }

        $this->assertEquals($count, count($nodes));
    }
}
