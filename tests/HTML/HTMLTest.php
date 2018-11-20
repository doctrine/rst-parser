<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\HTML;

use Doctrine\RST\Nodes\DocumentNode;
use Doctrine\RST\Parser;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;
use function htmlspecialchars;
use function substr_count;

/**
 * Unit testing for RST
 */
class HTMLTest extends TestCase
{
    /**
     * Test some links demo
     */
    public function testLinks() : void
    {
        $document = $this->parseHTML('links.rst');

        self::assertContains('<a href="http://docs.doctrine-project.org/en/latest/tutorials/embeddables.html">in the documentation</a>', $document);
        self::assertContains('<a href="http://www.google.com/">', $document);
        self::assertContains('<a href="http://xkcd.com/">', $document);
        self::assertContains('<a href="http://something.com/">', $document);
        self::assertContains('<a href="http://anonymous.com/">', $document);
        self::assertContains('<a href="http://www.github.com/">', $document);
        self::assertContains('under_score', $document);
        self::assertContains(' spacy', $document);
        self::assertNotContains(' ,', $document);
        self::assertNotContains('`', $document);
    }

    /**
     * Testing the non breakable spaces (~)
     */
    public function testNbsp() : void
    {
        $document = $this->parseHTML('nbsp.rst');

        self::assertContains('&nbsp;', $document);
        self::assertNotContains('~', $document);
    }

    /**
     * Testing that the text is ecaped
     */
    public function testEscape() : void
    {
        $document = $this->parseHTML('escape.rst');

        self::assertContains('&lt;script&gt;', $document);
        self::assertNotContains('<script>', $document);
    }

    /**
     * Testing the emphasis
     */
    public function testEmphasis() : void
    {
        $document = $this->parseHTML('italic.rst');

        self::assertContains('<em>italic emphasis</em>', $document);

        $document = $this->parseHTML('strong.rst');

        self::assertContains('<strong>strong emphasis</strong>', $document);
    }

    /**
     * Testing a table
     */
    public function testTable() : void
    {
        $document = $this->parseHTML('simple-table.rst');

        self::assertSame(1, substr_count($document, '<table>'));
        self::assertSame(1, substr_count($document, '</table>'));
        self::assertSame(3, substr_count($document, '<tr>'));
        self::assertSame(3, substr_count($document, '</tr>'));
        self::assertSame(6, substr_count($document, '<td'));
        self::assertSame(6, substr_count($document, '</td>'));
        self::assertSame(3, substr_count($document, '<th>'));
        self::assertSame(3, substr_count($document, '</th>'));
        self::assertSame(1, substr_count($document, '<tbody>'));
        self::assertSame(1, substr_count($document, '</tbody>'));
        self::assertSame(1, substr_count($document, '<thead>'));
        self::assertSame(1, substr_count($document, '</thead>'));
        self::assertNotContains('==', $document);
        self::assertContains('First col', $document);
        self::assertContains('Last col', $document);

        $document = $this->parseHTML('pretty-table-no-header.rst');

        self::assertSame(1, substr_count($document, '<table>'));
        self::assertSame(1, substr_count($document, '</table>'));
        self::assertSame(2, substr_count($document, '<tr>'));
        self::assertSame(2, substr_count($document, '</tr>'));
        self::assertSame(6, substr_count($document, '<td'));
        self::assertSame(6, substr_count($document, '</td>'));
        self::assertSame(1, substr_count($document, '<tbody>'));
        self::assertSame(1, substr_count($document, '</tbody>'));
        self::assertSame(0, substr_count($document, '<thead>'));
        self::assertSame(0, substr_count($document, '</thead>'));
        self::assertNotContains('--', $document);
        self::assertNotContains('+', $document);
        self::assertNotContains('|', $document);
        self::assertContains('Some', $document);
        self::assertContains('Data', $document);

        $document = $this->parseHTML('pretty-table-header.rst');

        self::assertSame(1, substr_count($document, '<thead>'));
        self::assertSame(1, substr_count($document, '</thead>'));
        self::assertSame(2, substr_count($document, '<th>'));
        self::assertSame(2, substr_count($document, '</th>'));
        self::assertNotContains('==', $document);
    }

    public function testTableError() : void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Malformed table');

        $this->parseHTML('simple-table-error.rst');
    }

    /**
     * Testing HTML table with headers
     */
    public function testTableWithNestedList() : void
    {
        $document = $this->parseHTML('table-nested-list.rst');

        self::assertSame(1, substr_count($document, '<ul>'));
        self::assertSame(1, substr_count($document, '</ul>'));
        self::assertSame(4, substr_count($document, '<li'));
        self::assertSame(4, substr_count($document, '</li>'));
        self::assertNotContains('- ', $document);
    }

    /**
     * Testing literals
     */
    public function testLiteral() : void
    {
        $document = $this->parseHTML('literal.rst');

        $code = 'this is a *boring* literal `a`_ containing some dirty things <3 hey_ !';
        self::assertContains(htmlspecialchars($code), $document);
        self::assertSame(1, substr_count($document, '<code>'));
        self::assertSame(1, substr_count($document, '</code>'));
    }

    /**
     * Testing separators
     */
    public function testSeparator() : void
    {
        $document = $this->parseHTML('separator.rst');

        self::assertContains('<hr />', $document);
    }

    /**
     * Testing the images feature
     */
    public function testImage() : void
    {
        $document = $this->parseHTML('image.rst');

        self::assertContains('<img', $document);
        self::assertContains('src="test.jpg"', $document);
        self::assertContains('src="try.jpg"', $document);
        self::assertContains('src="other.jpg"', $document);
        self::assertContains('width="123"', $document);
        self::assertContains('title="Other"', $document);
        self::assertNotContains('..', $document);
        self::assertNotContains('image', $document);
        self::assertNotContains('::', $document);

        $document = $this->parseHTML('image-inline.rst');

        self::assertContains('<img', $document);
        self::assertContains('src="test.jpg"', $document);
    }

    /**
     * Testing figure directive
     */
    public function testFigure() : void
    {
        $document = $this->parseHTML('figure.rst');

        self::assertContains('<figure>', $document);
        self::assertContains('<img', $document);
        self::assertContains('src="foo.jpg"', $document);
        self::assertContains('width="100"', $document);
        self::assertContains('<figcaption>', $document);
        self::assertContains('This is a foo!', $document);
        self::assertContains('</figcaption>', $document);
    }

    /**
     * Testing that an image that just directly follows some text works
     */
    public function testImageFollow() : void
    {
        $document = $this->parseHTML('image-follow.rst');

        self::assertSame(1, substr_count($document, '<img'));
        self::assertSame(1, substr_count($document, '"img/test.jpg"'));
    }

    /**
     * Testing a list
     */
    public function testList() : void
    {
        $document = $this->parseHTML('list.rst');

        self::assertSame(1, substr_count($document, '<ul>'));
        self::assertSame(1, substr_count($document, '</ul>'));
        self::assertNotContains('<ol>', $document);
        self::assertSame(4, substr_count($document, '<li>'));
        self::assertSame(4, substr_count($document, '</li>'));
        self::assertNotContains('*', $document);
        self::assertContains('This is', $document);
        self::assertContains('Last line', $document);

        $document = $this->parseHTML('indented-list.rst');

        self::assertSame(1, substr_count($document, '<ul>'));
        self::assertSame(1, substr_count($document, '</ul>'));
        self::assertNotContains('<ol>', $document);
        self::assertSame(4, substr_count($document, '<li>'));
        self::assertSame(4, substr_count($document, '</li>'));
        self::assertNotContains('*', $document);
        self::assertContains('This is', $document);

        $document = $this->parseHTML('ordered.rst');

        self::assertSame(1, substr_count($document, '<ol>'));
        self::assertSame(1, substr_count($document, '</ol>'));
        self::assertNotContains('<ul>', $document);
        self::assertSame(3, substr_count($document, '<li>'));
        self::assertSame(3, substr_count($document, '</li>'));
        self::assertNotContains('.', $document);
        self::assertContains('First item', $document);

        $document = $this->parseHTML('ordered2.rst');

        self::assertSame(1, substr_count($document, '<ol>'));
        self::assertSame(1, substr_count($document, '</ol>'));
        self::assertNotContains('<ul>', $document);
        self::assertSame(3, substr_count($document, '<li>'));
        self::assertSame(3, substr_count($document, '</li>'));
        self::assertNotContains('.', $document);
        self::assertContains('First item', $document);

        $document = $this->parseHTML('list-empty.rst');
        self::assertSame(1, substr_count($document, '<ol>'));
        self::assertSame(1, substr_count($document, '</ol>'));
        self::assertSame(1, substr_count($document, '<ul>'));
        self::assertSame(1, substr_count($document, '</ul>'));
        self::assertSame(5, substr_count($document, '<li>'));
        self::assertSame(5, substr_count($document, '</li>'));
        self::assertContains('<p>This is not in the list</p>', $document);

        $document = $this->parseHTML('list-dash.rst');
        self::assertSame(1, substr_count($document, '<ul>'));
        self::assertSame(1, substr_count($document, '</ul>'));
        self::assertSame(2, substr_count($document, '<li class="dash">'));
        self::assertSame(2, substr_count($document, '</li>'));

        $document = $this->parseHTML('list-alternate-syntax.rst');
        self::assertSame(1, substr_count($document, '<ul>'));
        self::assertSame(1, substr_count($document, '</ul>'));
        self::assertSame(3, substr_count($document, '<li class="dash">'));
        self::assertSame(3, substr_count($document, '</li>'));
    }

    public function testEmptyParagraph() : void
    {
        $document = $this->parseHTML('empty-p.rst');

        self::assertNotContains('<p></p>', $document);
    }

    /**
     * Testing css stylesheet
     */
    public function testStylesheet() : void
    {
        $document = $this->parseHTML('css.rst');

        self::assertContains('<link rel="stylesheet" type="text/css" href="style.css"', $document);
    }

    /**
     * Testing a title that follows a wrapping directive
     */
    public function testTitleFollowDirective() : void
    {
        $document = $this->parseHTML('directive-title.rst');

        self::assertSame(1, substr_count($document, '<div class="note'));
        self::assertSame(1, substr_count($document, '<h1>'));
        self::assertSame(1, substr_count($document, '</h1>'));
    }

    /**
     * Block quotes run a parse and thus can mess with environment, a bug was fixed
     * and this test avoid it to be reproduced
     */
    public function testQuoteResetTitles() : void
    {
        $document = $this->parseHTML('quote-title.rst');

        self::assertSame(1, substr_count($document, '<h1>Title</h1>'));
        self::assertSame(1, substr_count($document, '<h2>Another title</h2>'));
    }

    /**
     * Testing quote
     */
    public function testQuote() : void
    {
        $document = $this->parseHTML('quote.rst');

        self::assertSame(1, substr_count($document, '<blockquote>'));
        self::assertContains('<p>', $document);
        self::assertContains('</p>', $document);
        self::assertSame(1, substr_count($document, '</blockquote>'));

        $document = $this->parseHTML('quote2.rst');

        self::assertSame(1, substr_count($document, '<blockquote>'));
        self::assertContains('<p>', $document);
        self::assertContains('</p>', $document);
        self::assertSame(1, substr_count($document, '</blockquote>'));
        self::assertSame(1, substr_count($document, '<strong>'));
        self::assertSame(1, substr_count($document, '</strong>'));
        self::assertNotContains('*', $document);

        $document = $this->parseHTML('quote3.rst');

        self::assertSame(1, substr_count($document, '<blockquote>'));
        self::assertContains('<p>', $document);
        self::assertContains('</p>', $document);
        self::assertSame(1, substr_count($document, '</blockquote>'));
        self::assertSame(1, substr_count($document, '<img'));
    }

    /**
     * Testing code blocks
     */
    public function testCode() : void
    {
        $document = $this->parseHTML('code.rst');

        self::assertSame(1, substr_count($document, '<pre>'));
        self::assertSame(1, substr_count($document, '</pre>'));
        self::assertSame(1, substr_count($document, '<code'));
        self::assertSame(1, substr_count($document, '</code>'));
        self::assertContains('This is a code block', $document);
        self::assertNotContains('::', $document);
        self::assertNotContains('<br', $document);

        $document = $this->parseHTML('code-block.rst');

        self::assertSame(1, substr_count($document, '<pre>'));
        self::assertSame(1, substr_count($document, '</pre>'));
        self::assertSame(1, substr_count($document, '<code'));
        self::assertSame(1, substr_count($document, '</code>'));
        $code = 'cout << "Hello world!" << endl;';
        self::assertContains(htmlspecialchars($code), $document);

        $document = $this->parseHTML('code-java.rst');

        self::assertSame(1, substr_count($document, '<pre>'));
        self::assertSame(1, substr_count($document, '</pre>'));
        self::assertSame(1, substr_count($document, '<code class="java"'));
        self::assertSame(1, substr_count($document, '</code>'));

        $document = $this->parseHTML('code-list.rst');

        self::assertSame(1, substr_count($document, '<pre>'));
        self::assertSame(1, substr_count($document, '</pre>'));
        self::assertContains('*', $document);
    }

    /**
     * Testing titles
     */
    public function testTitles() : void
    {
        $document = $this->parseHTML('titles.rst');

        self::assertSame(1, substr_count($document, '<h1>'));
        self::assertSame(1, substr_count($document, '<h1>'));
        self::assertSame(3, substr_count($document, '<h2>'));
        self::assertSame(3, substr_count($document, '</h2>'));
        self::assertSame(4, substr_count($document, '<h3>'));
        self::assertSame(4, substr_count($document, '</h3>'));
        self::assertContains('<a id="main-title"></a><h1>Main title</h1>', $document);
        self::assertContains('<a id="first-subtitle"></a><h2>First subtitle</h2>', $document);
        self::assertContains('<a id="first-subsubtitle"></a><h3>First subsubtitle</h3>', $document);
        self::assertContains('<a id="second-subsubtitle"></a><h3>Second subsubtitle</h3>', $document);
        self::assertContains('<a id="third-subsubtitle"></a><h3>Third subsubtitle</h3>', $document);
        self::assertContains('<a id="fourth-subsubtitle"></a><h3>Fourth subsubtitle</h3>', $document);
        self::assertContains('<a id="em"></a><h2>em</h2>', $document);
        self::assertNotContains('==', $document);
        self::assertNotContains('--', $document);
        self::assertNotContains('~~', $document);
    }

    public function testTitlesAuto() : void
    {
        $document = $this->parseHTML('titles-auto.rst');

        self::assertSame(1, substr_count($document, '<h1>'));
        self::assertSame(1, substr_count($document, '<h1>'));
        self::assertSame(2, substr_count($document, '<h2>'));
        self::assertSame(2, substr_count($document, '</h2>'));
        self::assertSame(4, substr_count($document, '<h3>'));
        self::assertSame(4, substr_count($document, '</h3>'));
        self::assertContains('<a id="main-title"></a>', $document);
        self::assertNotContains('==', $document);
        self::assertNotContains('--', $document);
        self::assertNotContains('~~', $document);
    }

    /**
     * Testing that a wrapper node can be at end of file
     */
    public function testWrapperNodeEnd() : void
    {
        $document = $this->parseHTML('wrap.rst');

        self::assertSame(1, substr_count($document, 'note'));
    }

    /**
     * Tests a variable used with a wrap sub directive
     */
    public function testVariableWrap() : void
    {
        $document = $this->parseHTML('variable-wrap.rst');

        self::assertSame(2, substr_count($document, 'note'));
        self::assertSame(2, substr_count($document, 'important'));
    }

    public function testReferenceMatchingIsntTooEager() : void
    {
        // Before, it would render
        // <p><code>:doc:`lorem</code><a href="https://consectetur.org"> and 249a92befe90adcd3bb404a91d4e1520a17a8b56` sit `amet</a></p>

        self::assertSame(
            "<p><code>:doc:`lorem`</code> and <code>:code:`what`</code> sit <a href=\"https://consectetur.org\">amet</a></p>\n",
            $this->parse('no-eager-literals.rst')->render()
        );
    }

    public function testUnknownDirective() : void
    {
        $this->expectException(Throwable::class);
        $this->expectExceptionMessage('Unknown directive: unknown-directive');

        $this->parseHTML('unknown-directive.rst');
    }

    /**
     * Testing div directive
     */
    public function testDivDirective() : void
    {
        $document = $this->parseHTML('div.rst');

        self::assertSame(1, substr_count($document, '<div'));
        self::assertSame(1, substr_count($document, 'class="testing"'));
        self::assertSame(1, substr_count($document, 'Hello!'));
        self::assertSame(1, substr_count($document, '</div>'));
    }

    /**
     * Testing that comments starting by ... are not handled as comments
     */
    public function testCommentThree() : void
    {
        $document = $this->parseHTML('comment-3.rst');

        self::assertSame(1, substr_count($document, '... This is not a comment!'));
        self::assertSame(0, substr_count($document, 'This is a comment!'));
    }

    /**
     * Testing crlf
     */
    public function testCRLF() : void
    {
        $document = $this->parseHTML('crlf.rst');

        self::assertSame(1, substr_count($document, '<h1>'), 'CRLF should be supported');
    }

    /**
     * Testing that emphasis and span elements are evaluated in links
     */
    public function testLinkSpan() : void
    {
        $document = $this->parseHTML('link-span.rst');

        self::assertSame(1, substr_count($document, '<strong>'));
    }

    /**
     * Testing removing BOM
     */
    public function testBom() : void
    {
        $document = $this->parseHTML('bom.rst');
        self::assertNotContains('Should be a comment', $document);
    }

    /**
     * Testing with a raw directive
     */
    public function testRaw() : void
    {
        $document = $this->parseHTML('raw.rst');
        self::assertContains('<u>Underlined!</u>', $document);
    }

    public function testAnchors() : void
    {
        $document = $this->parseHTML('anchor.rst');

        self::assertContains('<a id="anchors"></a><h1>Anchors</h1>', $document);
        self::assertContains('<a id="anchors"></a><h1>Anchors</h1>', $document);
        self::assertContains('<a id="lists"></a>', $document);
        self::assertContains('<p><a href="#lists">go to lists</a></p>', $document);
    }

    public function testInvalidAnchor() : void
    {
        $this->expectException(Throwable::class);
        $this->expectExceptionMessage('Found invalid reference "@Anchor Section"');

        $document = $this->parse('anchor-failure.rst');

        $rendered = $document->renderDocument();
    }

    public function testLinkWithNewLine() : void
    {
        $document = $this->parse('link-with-new-line.rst');

        $rendered = $document->renderDocument();

        self::assertContains(
            '<a href="https://www.doctrine-project.org/projects/rst-parser.html">link to the doc</a>',
            $rendered
        );
    }

    public function testLinkWithNewLineInsideList() : void
    {
        $document = $this->parse('link-with-new-line-inside-list.rst');

        $rendered = $document->renderDocument();

        self::assertContains(
            '<a href="https://www.doctrine-project.org/projects/rst-parser.html">link to the doc</a>',
            $rendered
        );
    }

    public function testLinkWithNoName() : void
    {
        $document = $this->parse('link-with-no-name.rst');

        $rendered = $document->renderDocument();

        self::assertContains(
            '<a href="https://github.com/symfony/form">https://github.com/symfony/form</a>',
            $rendered
        );
    }

    public function testLinkWithSpecialChar() : void
    {
        $document = $this->parse('link-with-special-char.rst');

        $rendered = $document->renderDocument();

        self::assertContains(
            '<a href="https://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants">IntlDateFormatter::MEDIUM</a>',
            $rendered
        );

        self::assertContains(
            '<a href="https://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants">IntlDateFormatter:: MEDIUM</a>',
            $rendered
        );
    }

    public function testClassDirective() : void
    {
        $document = $this->parse('class-directive.rst');

        $rendered = $document->renderDocument();

        self::assertContains('<p class="special-paragraph1">Test special-paragraph1 1.</p>', $rendered);

        self::assertContains('<p>Test special-paragraph1 2.</p>', $rendered);

        self::assertContains('<p class="special-paragraph2">Test special-paragraph2 1.</p>', $rendered);
        self::assertContains('<p class="special-paragraph2">Test special-paragraph2 2.</p>', $rendered);

        self::assertContains('<div class="note"><p class="special-paragraph3">Test</p>', $rendered);

        self::assertContains('<ul class="special-list"><li class="dash">Test list item 1.</li>', $rendered);

        self::assertContains('<p class="rot-gelb-blau grun-2008">Weird class names.</p>', $rendered);

        self::assertContains('<p class="level1">Level 1</p>', $rendered);

        self::assertContains('<blockquote class="level1"><p class="level2">Level2 1</p>', $rendered);

        self::assertContains('<p class="level2">Level2 2</p>', $rendered);

        self::assertContains('<dl class="special-definition-list"><dt>term 1</dt><dd>Definition 1 </dd></dl>', $rendered);

        self::assertContains('<table class="special-table">', $rendered);
    }

    /**
     * Helper function, parses a file and returns the document
     * produced by the parser
     */
    private function parse(string $file) : DocumentNode
    {
        $directory   = __DIR__ . '/files/';
        $parser      = new Parser();
        $environment = $parser->getEnvironment();
        $environment->setCurrentDirectory($directory);

        return $parser->parseFile($directory . $file);
    }

    private function parseHTML(string $file) : string
    {
        return $this->parse($file)->renderDocument();
    }
}
