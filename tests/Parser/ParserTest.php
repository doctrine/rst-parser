<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\Parser;

use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Nodes\DocumentNode;
use Doctrine\RST\Nodes\DummyNode;
use Doctrine\RST\Nodes\ListNode;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\ParagraphNode;
use Doctrine\RST\Nodes\QuoteNode;
use Doctrine\RST\Nodes\TableNode;
use Doctrine\RST\Nodes\TitleNode;
use Doctrine\RST\Parser;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function assert;
use function count;
use function file_get_contents;
use function sprintf;
use function trim;

/**
 * Unit testing for RST
 */
class ParserTest extends TestCase
{
    public function testGetSubParserPassesConfiguration(): void
    {
        $parser = new Parser();

        $configuration = $parser->getEnvironment()->getConfiguration();

        $subParser = $parser->getSubParser();

        self::assertSame($configuration, $subParser->getEnvironment()->getConfiguration());
    }

    /**
     * Testing that code node value is good
     */
    public function testCodeNode(): void
    {
        $document = $this->parse('code-block-lastline.rst');

        $nodes = $document->getNodes(static function ($node) {
            return $node instanceof CodeNode;
        });

        self::assertSame(1, count($nodes));
        self::assertSame("A\nB\n C", trim($nodes[0]->getValueString()));
    }

    /**
     * Testing paragraph nodes
     */
    public function testParagraphNode(): void
    {
        $document = $this->parse('paragraph.rst');

        self::assertHasNode($document, static function ($node) {
            return $node instanceof ParagraphNode;
        }, 1);
        self::assertContains('Hello world!', $document->render());
    }

    /**
     * Testing multi-paragraph nodes
     */
    public function testParagraphNodes(): void
    {
        $document = $this->parse('paragraphs.rst');

        self::assertHasNode($document, static function ($node) {
            return $node instanceof ParagraphNode;
        }, 3);
    }

    /**
     * Testing quote and block code
     */
    public function testBlockNode(): void
    {
        $quote = $this->parse('quote.rst');

        self::assertHasNode($quote, static function ($node) {
            return $node instanceof QuoteNode;
        }, 1);

        $code = $this->parse('code.rst');

        self::assertHasNode($quote, static function ($node) {
            return $node instanceof QuoteNode;
        }, 1);

        self::assertNotContains('::', $code->render());
    }

    /**
     * Testing the titling
     */
    public function testTitles(): void
    {
        $document = $this->parse('title.rst');

        self::assertHasNode($document, static function ($node) {
            return $node instanceof TitleNode
                && $node->getLevel() === 1;
        }, 1);

        $document = $this->parse('title2.rst');

        self::assertHasNode($document, static function ($node) {
            return $node instanceof TitleNode
                && $node->getLevel() === 2;
        }, 1);
    }

    /**
     * Testing the titling
     */
    public function testList(): void
    {
        $document = $this->parse('list.rst');

        self::assertHasNode($document, static function ($node) {
            return $node instanceof ListNode;
        }, 1);

        $document = $this->parse('indented-list.rst');

        self::assertHasNode($document, static function ($node) {
            return $node instanceof ListNode;
        }, 1);

        $document = $this->parse('list-empty.rst');
        self::assertHasNode($document, static function ($node) {
            return $node instanceof ListNode;
        }, 1);
    }

    /**
     * Testing the titles retrieving
     */
    public function testGetTitles(): void
    {
        $document = $this->parse('titles.rst');

        self::assertSame($document->getTitle(), 'The main title');
        self::assertSame($document->getTitles(), [
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
    public function testTable(): void
    {
        $document = $this->parse('table.rst');

        $nodes = $document->getNodes(static function ($node) {
            return $node instanceof TableNode;
        });

        self::assertSame(count($nodes), 1);

        if ($nodes !== []) {
            $table = $nodes[0];
            assert($table instanceof TableNode);

            self::assertSame(3, $table->getCols());
            self::assertSame(3, $table->getRows());
        }

        $document = $this->parse('pretty-table.rst');

        $nodes = $document->getNodes(static function ($node) {
            return $node instanceof TableNode;
        });

        self::assertSame(count($nodes), 1);

        if ($nodes === []) {
            return;
        }

        $table = $nodes[0];
        assert($table instanceof TableNode);

        self::assertSame(3, $table->getCols());
        self::assertSame(2, $table->getRows());
    }

    /**
     * Tests that a simple replace works
     */
    public function testReplace(): void
    {
        $document = $this->parse('replace.rst');

        self::assertContains('Hello world!', $document->render());
    }

    /**
     * Test the include:: pseudo-directive
     */
    public function testInclusion(): void
    {
        $document = $this->parse('inclusion.rst');

        self::assertContains('I was actually included', $document->renderDocument());
    }

    public function testThrowExceptionOnInvalidFileInclude(): void
    {
        $parser      = new Parser();
        $environment = $parser->getEnvironment();

        $data = file_get_contents(__DIR__ . '/files/inclusion-bad.rst');

        self::assertInternalType('string', $data);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Include ".. include:: non-existent-file.rst" does not exist or is not readable.');

        $parser->parse($data);
    }

    public function testDirective(): void
    {
        $document = $this->parse('directive.rst');

        $nodes = $document->getNodes(static function ($node) {
            return $node instanceof DummyNode;
        });

        self::assertSame(1, count($nodes));

        if ($nodes === []) {
            return;
        }

        $node = $nodes[0];
        assert($node instanceof DummyNode);

        $data = $node->data;

        self::assertSame('some data', $data['data']);
        $options = $data['options'];
        self::assertTrue(isset($options['maxdepth']));
        self::assertTrue(isset($options['titlesonly']));
        self::assertTrue(isset($options['glob']));
        self::assertTrue($options['titlesonly']);
        self::assertSame('123', $options['maxdepth']);
    }

    public function testSubsequentParsesDontHaveTheSameTitleLevelOrder(): void
    {
        $directory = __DIR__ . '/files';

        $parser = new Parser();
        $parser->getEnvironment()->setCurrentDirectory($directory);

        /** @var TitleNode[] $nodes1 */
        $nodes1 = $parser->parseFile(sprintf('%s/mixed-titles-1.rst', $directory))->getNodes();
        /** @var TitleNode[] $nodes2 */
        $nodes2 = $parser->parseFile(sprintf('%s/mixed-titles-2.rst', $directory))->getNodes();

        $node = $nodes1[1];
        assert($node instanceof TitleNode);
        self::assertSame(1, $node->getLevel());

        $node = $nodes1[3];
        assert($node instanceof TitleNode);
        self::assertSame(2, $node->getLevel());

        $node = $nodes2[1];
        assert($node instanceof TitleNode);
        self::assertSame(1, $node->getLevel(), 'Title level in second parse is influenced by first parse');

        $node = $nodes2[3];
        assert($node instanceof TitleNode);
        self::assertSame(2, $node->getLevel(), 'Title level in second parse is influenced by first parse');
    }

    public function testNewlineBeforeAnIncludedIsntGobbled(): void
    {
        /** @var Node[] $nodes */
        $nodes = $this->parse('inclusion-newline.rst')->getNodes();

        self::assertCount(5, $nodes);
        self::assertInstanceOf('Doctrine\RST\Nodes\SectionBeginNode', $nodes[0]);
        self::assertInstanceOf('Doctrine\RST\Nodes\TitleNode', $nodes[1]);
        self::assertInstanceOf('Doctrine\RST\Nodes\ParagraphNode', $nodes[2]);
        self::assertInstanceOf('Doctrine\RST\Nodes\ParagraphNode', $nodes[3]);
        self::assertContains('<p>Test this paragraph is present.</p>', $nodes[2]->render());
        self::assertContains('<p>And this one as well.</p>', $nodes[3]->render());
    }

    public function testIncludesKeepScope(): void
    {
        // See http://docutils.sourceforge.net/docs/ref/rst/directives.html#including-an-external-document-fragment

        /** @var Node[] $nodes */
        $nodes = $this->parse('inclusion-scope.rst')->getNodes();

        self::assertCount(4, $nodes);

        $node = $nodes[0]->getValue();
        assert($node instanceof Node);
        self::assertSame("This first example will be parsed at the document level, and can\nthus contain any construct, including section headers.", $node->render());

        $node = $nodes[1]->getValue();
        assert($node instanceof Node);
        self::assertSame('This is included.', $node->render());

        $node = $nodes[2]->getValue();
        assert($node instanceof Node);
        self::assertSame('Back in the main document.', $node->render());

        self::assertInstanceOf('Doctrine\RST\Nodes\QuoteNode', $nodes[3]);

        $node = $nodes[3]->getValue();
        assert($node instanceof Node);
        self::assertContains('This is included.', $node->render());
    }

    public function testIncludesPolicy(): void
    {
        $directory   = __DIR__ . '/files/';
        $parser      = new Parser();
        $environment = $parser->getEnvironment();
        $environment->setCurrentDirectory($directory);

        // Test defaults
        self::assertTrue($parser->getIncludeAllowed());
        self::assertSame('', $parser->getIncludeRoot());

        // Default policy:
        $document = $parser->parseFile($directory . 'inclusion-policy.rst')->render();
        self::assertContains('SUBDIRECTORY OK', $document);
        self::assertContains('EXTERNAL FILE INCLUDED!', $document);

        // Disbaled policy:
        $parser->setIncludePolicy(false);
        $nodes = $parser->parseFile($directory . 'inclusion-policy.rst')->getNodes();
        self::assertCount(1, $nodes);

        // Enabled
        $parser->setIncludePolicy(true);
        $nodes = $parser->parseFile($directory . 'inclusion-policy.rst')->getNodes();
        self::assertCount(6, $nodes);

        // Jailed
        $parser->setIncludePolicy(true, $directory);
        $nodes = $parser->parseFile($directory . 'inclusion-policy.rst')->getNodes();
        self::assertCount(5, $nodes);
    }

    public function testParseFileThrowsInvalidArgumentExceptionForMissingFile(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('File at path does-not-exist.rst does not exist');

        $parser = new Parser();
        $parser->parseFile('does-not-exist.rst');
    }

    /**
     * Helper function, parses a file and returns the document
     * produced by the parser
     */
    private function parse(string $file): DocumentNode
    {
        $directory   = __DIR__ . '/files/';
        $parser      = new Parser();
        $environment = $parser->getEnvironment();
        $environment->setCurrentDirectory($directory);

        $data = file_get_contents($directory . $file);

        if ($data === false) {
            throw new Exception('Could not open file.');
        }

        return $parser->parse($data);
    }

    /**
     * Asserts that a document has nodes that satisfy the function
     */
    private function assertHasNode(DocumentNode $document, callable $function, ?int $count = null): void
    {
        $nodes = $document->getNodes($function);
        self::assertNotEmpty($nodes);

        if ($count === null) {
            return;
        }

        self::assertSame($count, count($nodes));
    }
}
