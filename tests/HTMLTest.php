<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Document;
use Doctrine\RST\Parser;
use Exception;
use PHPUnit\Framework\TestCase;
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
        $document = $this->parseHTML('table.rst');

        self::assertEquals(1, substr_count($document, '<table class="table table-bordered">'));
        self::assertEquals(1, substr_count($document, '</table>'));
        self::assertEquals(2, substr_count($document, '<tr>'));
        self::assertEquals(2, substr_count($document, '</tr>'));
        self::assertEquals(6, substr_count($document, '<td'));
        self::assertEquals(6, substr_count($document, '</td>'));
        self::assertNotContains('==', $document);
        self::assertContains('First col', $document);
        self::assertContains('Last col', $document);

        $document = $this->parseHTML('pretty-table.rst');

        self::assertEquals(1, substr_count($document, '<table class="table table-bordered">'));
        self::assertEquals(1, substr_count($document, '</table>'));
        self::assertEquals(2, substr_count($document, '<tr>'));
        self::assertEquals(2, substr_count($document, '</tr>'));
        self::assertEquals(6, substr_count($document, '<td'));
        self::assertEquals(6, substr_count($document, '</td>'));
        self::assertNotContains('--', $document);
        self::assertNotContains('+', $document);
        self::assertNotContains('|', $document);
        self::assertContains('Some', $document);
        self::assertContains('Data', $document);
    }

    /**
     * Testing HTML table with headers
     */
    public function testHeaderTable() : void
    {
        $document = $this->parseHTML('table2.rst');

        self::assertEquals(2, substr_count($document, '<th>'));
        self::assertEquals(2, substr_count($document, '</th>'));
        self::assertNotContains('==', $document);
    }

    /**
     * Testing literals
     */
    public function testLiteral() : void
    {
        $document = $this->parseHTML('literal.rst');

        $code = 'this is a *boring* literal `a`_ containing some dirty things <3 hey_ !';
        self::assertContains(htmlspecialchars($code), $document);
        self::assertEquals(1, substr_count($document, '<code>'));
        self::assertEquals(1, substr_count($document, '</code>'));
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

        self::assertEquals(1, substr_count($document, '<img'));
        self::assertEquals(1, substr_count($document, '"img/test.jpg"'));
    }

    /**
     * Testing a list
     */
    public function testList() : void
    {
        $document = $this->parseHTML('list.rst');

        self::assertEquals(1, substr_count($document, '<ul>'));
        self::assertEquals(1, substr_count($document, '</ul>'));
        self::assertNotContains('<ol>', $document);
        self::assertEquals(4, substr_count($document, '<li>'));
        self::assertEquals(4, substr_count($document, '</li>'));
        self::assertNotContains('*', $document);
        self::assertContains('This is', $document);
        self::assertContains('Last line', $document);

        $document = $this->parseHTML('indented-list.rst');

        self::assertEquals(1, substr_count($document, '<ul>'));
        self::assertEquals(1, substr_count($document, '</ul>'));
        self::assertNotContains('<ol>', $document);
        self::assertEquals(4, substr_count($document, '<li>'));
        self::assertEquals(4, substr_count($document, '</li>'));
        self::assertNotContains('*', $document);
        self::assertContains('This is', $document);

        $document = $this->parseHTML('ordered.rst');

        self::assertEquals(1, substr_count($document, '<ol>'));
        self::assertEquals(1, substr_count($document, '</ol>'));
        self::assertNotContains('<ul>', $document);
        self::assertEquals(3, substr_count($document, '<li>'));
        self::assertEquals(3, substr_count($document, '</li>'));
        self::assertNotContains('.', $document);
        self::assertContains('First item', $document);

        $document = $this->parseHTML('ordered2.rst');

        self::assertEquals(1, substr_count($document, '<ol>'));
        self::assertEquals(1, substr_count($document, '</ol>'));
        self::assertNotContains('<ul>', $document);
        self::assertEquals(3, substr_count($document, '<li>'));
        self::assertEquals(3, substr_count($document, '</li>'));
        self::assertNotContains('.', $document);
        self::assertContains('First item', $document);

        $document = $this->parseHTML('list-empty.rst');
        self::assertEquals(1, substr_count($document, '<ol>'));
        self::assertEquals(1, substr_count($document, '</ol>'));
        self::assertEquals(1, substr_count($document, '<ul>'));
        self::assertEquals(1, substr_count($document, '</ul>'));
        self::assertEquals(5, substr_count($document, '<li>'));
        self::assertEquals(5, substr_count($document, '</li>'));
        self::assertContains('<p>This is not in the list</p>', $document);

        $document = $this->parseHTML('list-dash.rst');
        self::assertEquals(1, substr_count($document, '<ul>'));
        self::assertEquals(1, substr_count($document, '</ul>'));
        self::assertEquals(2, substr_count($document, '<li class="dash">'));
        self::assertEquals(2, substr_count($document, '</li>'));

        $document = $this->parseHTML('list-alternate-syntax.rst');
        self::assertEquals(1, substr_count($document, '<ul>'));
        self::assertEquals(1, substr_count($document, '</ul>'));
        self::assertEquals(3, substr_count($document, '<li class="dash">'));
        self::assertEquals(3, substr_count($document, '</li>'));
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

        self::assertEquals(1, substr_count($document, '<div class="note'));
        self::assertEquals(1, substr_count($document, '<h1>'));
        self::assertEquals(1, substr_count($document, '</h1>'));
    }

    /**
     * Block quotes run a parse and thus can mess with environment, a bug was fixed
     * and this test avoid it to be reproduced
     */
    public function testQuoteResetTitles() : void
    {
        $document = $this->parseHTML('quote-title.rst');

        self::assertEquals(1, substr_count($document, '<h1>Title</h1>'));
        self::assertEquals(1, substr_count($document, '<h2>Another title</h2>'));
    }

    /**
     * Testing quote
     */
    public function testQuote() : void
    {
        $document = $this->parseHTML('quote.rst');

        self::assertEquals(1, substr_count($document, '<blockquote>'));
        self::assertContains('<p>', $document);
        self::assertContains('</p>', $document);
        self::assertEquals(1, substr_count($document, '</blockquote>'));

        $document = $this->parseHTML('quote2.rst');

        self::assertEquals(1, substr_count($document, '<blockquote>'));
        self::assertContains('<p>', $document);
        self::assertContains('</p>', $document);
        self::assertEquals(1, substr_count($document, '</blockquote>'));
        self::assertEquals(1, substr_count($document, '<strong>'));
        self::assertEquals(1, substr_count($document, '</strong>'));
        self::assertNotContains('*', $document);

        $document = $this->parseHTML('quote3.rst');

        self::assertEquals(1, substr_count($document, '<blockquote>'));
        self::assertContains('<p>', $document);
        self::assertContains('</p>', $document);
        self::assertEquals(1, substr_count($document, '</blockquote>'));
        self::assertEquals(1, substr_count($document, '<img'));
    }

    /**
     * Testing code blocks
     */
    public function testCode() : void
    {
        $document = $this->parseHTML('code.rst');

        self::assertEquals(1, substr_count($document, '<pre>'));
        self::assertEquals(1, substr_count($document, '</pre>'));
        self::assertEquals(1, substr_count($document, '<code'));
        self::assertEquals(1, substr_count($document, '</code>'));
        self::assertContains('This is a code block', $document);
        self::assertNotContains('::', $document);
        self::assertNotContains('<br', $document);

        $document = $this->parseHTML('code-block.rst');

        self::assertEquals(1, substr_count($document, '<pre>'));
        self::assertEquals(1, substr_count($document, '</pre>'));
        self::assertEquals(1, substr_count($document, '<code'));
        self::assertEquals(1, substr_count($document, '</code>'));
        $code = 'cout << "Hello world!" << endl;';
        self::assertContains(htmlspecialchars($code), $document);

        $document = $this->parseHTML('code-java.rst');

        self::assertEquals(1, substr_count($document, '<pre>'));
        self::assertEquals(1, substr_count($document, '</pre>'));
        self::assertEquals(1, substr_count($document, '<code class="java"'));
        self::assertEquals(1, substr_count($document, '</code>'));

        $document = $this->parseHTML('code-list.rst');

        self::assertEquals(1, substr_count($document, '<pre>'));
        self::assertEquals(1, substr_count($document, '</pre>'));
        self::assertContains('*', $document);
    }

    /**
     * Testing titles
     */
    public function testTitles() : void
    {
        $document = $this->parseHTML('titles.rst');

        self::assertEquals(1, substr_count($document, '<h1>'));
        self::assertEquals(1, substr_count($document, '<h1>'));
        self::assertEquals(2, substr_count($document, '<h2>'));
        self::assertEquals(2, substr_count($document, '</h2>'));
        self::assertEquals(4, substr_count($document, '<h3>'));
        self::assertEquals(4, substr_count($document, '</h3>'));
        self::assertContains('<a id="main-title"></a><h1>Main title</h1>', $document);
        self::assertContains('<a id="first-subtitle"></a><h2>First subtitle</h2>', $document);
        self::assertContains('<a id="first-subsubtitle"></a><h3>First subsubtitle</h3>', $document);
        self::assertContains('<a id="second-subsubtitle"></a><h3>Second subsubtitle</h3>', $document);
        self::assertContains('<a id="third-subsubtitle"></a><h3>Third subsubtitle</h3>', $document);
        self::assertContains('<a id="fourth-subsubtitle"></a><h3>Fourth subsubtitle</h3>', $document);
        self::assertNotContains('==', $document);
        self::assertNotContains('--', $document);
        self::assertNotContains('~~', $document);
    }

    public function testTitlesAuto() : void
    {
        $document = $this->parseHTML('titles-auto.rst');

        self::assertEquals(1, substr_count($document, '<h1>'));
        self::assertEquals(1, substr_count($document, '<h1>'));
        self::assertEquals(2, substr_count($document, '<h2>'));
        self::assertEquals(2, substr_count($document, '</h2>'));
        self::assertEquals(4, substr_count($document, '<h3>'));
        self::assertEquals(4, substr_count($document, '</h3>'));
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

        self::assertEquals(1, substr_count($document, 'note'));
    }

    /**
     * Tests a variable used with a wrap sub directive
     */
    public function testVariableWrap() : void
    {
        $document = $this->parseHTML('variable-wrap.rst');

        self::assertEquals(2, substr_count($document, 'note'));
        self::assertEquals(2, substr_count($document, 'important'));
    }

    public function testReferenceUnderDirective() : void
    {
        $document = $this->parseHTML('reference-directive.rst');

        self::assertEquals(1, substr_count($document, 'note'));
        self::assertEquals(1, substr_count($document, 'unresolved'));
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
        try {
            $document = $this->parseHTML('unknown-directive.rst');

            throw new Exception('This exception should not have been thrown.');
        } catch (Throwable $e) {
            $message = $e->getMessage();

            self::assertContains('unknown-directive.rst', $message);
            self::assertContains('line 2', $message);
        }
    }

    /**
     * Testing div directive
     */
    public function testDivDirective() : void
    {
        $document = $this->parseHTML('div.rst');

        self::assertEquals(1, substr_count($document, '<div'));
        self::assertEquals(1, substr_count($document, 'class="testing"'));
        self::assertEquals(1, substr_count($document, 'Hello!'));
        self::assertEquals(1, substr_count($document, '</div>'));
    }

    /**
     * Testing that comments starting by ... are not handled as comments
     */
    public function testCommentThree() : void
    {
        $document = $this->parseHTML('comment-3.rst');

        self::assertEquals(1, substr_count($document, '... This is not a comment!'));
        self::assertEquals(0, substr_count($document, 'This is a comment!'));
    }

    /**
     * Testing crlf
     */
    public function testCRLF() : void
    {
        $document = $this->parseHTML('crlf.rst');

        self::assertEquals(1, substr_count($document, '<h1>'), 'CRLF should be supported');
    }

    /**
     * Testing that emphasis and span elements are evaluated in links
     */
    public function testLinkSpan() : void
    {
        $document = $this->parseHTML('link-span.rst');

        self::assertEquals(1, substr_count($document, '<strong>'));
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
        self::assertContains('<p><a href="#anchor-section">@Anchor Section</a></p>', $document);
        self::assertContains('<a id="anchor-section"></a><h1>@Anchor Section</h1>', $document);
        self::assertContains('<a id="anchors"></a><h1>Anchors</h1>', $document);
        self::assertContains('<a id="lists"></a>', $document);
        self::assertContains('<p><a href="#lists">go to lists</a></p>', $document);
    }

    /**
     * Helper function, parses a file and returns the document
     * produced by the parser
     */
    private function parse(string $file) : Document
    {
        $directory   = __DIR__ . '/html/';
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
