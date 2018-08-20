<?php

declare(strict_types=1);

use Gregwar\RST\Parser;
use PHPUnit\Framework\TestCase;

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

        $this->assertContains('<a href="http://www.google.com/">', $document);
        $this->assertContains('<a href="http://xkcd.com/">', $document);
        $this->assertContains('<a href="http://something.com/">', $document);
        $this->assertContains('<a href="http://anonymous.com/">', $document);
        $this->assertContains('<a href="http://www.github.com/">', $document);
        $this->assertContains('under_score', $document);
        $this->assertContains(' spacy', $document);
        $this->assertNotContains(' ,', $document);
        $this->assertNotContains('`', $document);
    }

    /**
     * Testing the non breakable spaces (~)
     */
    public function testNbsp() : void
    {
        $document = $this->parseHTML('nbsp.rst');

        $this->assertContains('&nbsp;', $document);
        $this->assertNotContains('~', $document);
    }

    /**
     * Testing that the text is ecaped
     */
    public function testEscape() : void
    {
        $document = $this->parseHTML('escape.rst');

        $this->assertContains('&lt;script&gt;', $document);
        $this->assertNotContains('<script>', $document);
    }

    /**
     * Testing the emphasis
     */
    public function testEmphasis() : void
    {
        $document = $this->parseHTML('italic.rst');

        $this->assertContains('<em>italic emphasis</em>', $document);

        $document = $this->parseHTML('strong.rst');

        $this->assertContains('<strong>strong emphasis</strong>', $document);
    }

    /**
     * Testing a table
     */
    public function testTable() : void
    {
        $document = $this->parseHTML('table.rst');

        $this->assertEquals(1, substr_count($document, '<table class="table table-bordered">'));
        $this->assertEquals(1, substr_count($document, '</table>'));
        $this->assertEquals(2, substr_count($document, '<tr>'));
        $this->assertEquals(2, substr_count($document, '</tr>'));
        $this->assertEquals(6, substr_count($document, '<td'));
        $this->assertEquals(6, substr_count($document, '</td>'));
        $this->assertNotContains('==', $document);
        $this->assertContains('First col', $document);
        $this->assertContains('Last col', $document);

        $document = $this->parseHTML('pretty-table.rst');

        $this->assertEquals(1, substr_count($document, '<table class="table table-bordered">'));
        $this->assertEquals(1, substr_count($document, '</table>'));
        $this->assertEquals(2, substr_count($document, '<tr>'));
        $this->assertEquals(2, substr_count($document, '</tr>'));
        $this->assertEquals(6, substr_count($document, '<td'));
        $this->assertEquals(6, substr_count($document, '</td>'));
        $this->assertNotContains('--', $document);
        $this->assertNotContains('+', $document);
        $this->assertNotContains('|', $document);
        $this->assertContains('Some', $document);
        $this->assertContains('Data', $document);
    }

    /**
     * Testing HTML table with headers
     */
    public function testHeaderTable() : void
    {
        $document = $this->parseHTML('table2.rst');

        $this->assertEquals(2, substr_count($document, '<th>'));
        $this->assertEquals(2, substr_count($document, '</th>'));
        $this->assertNotContains('==', $document);
    }

    /**
     * Testing literals
     */
    public function testLiteral() : void
    {
        $document = $this->parseHTML('literal.rst');

        $code = 'this is a *boring* literal `a`_ containing some dirty things <3 hey_ !';
        $this->assertContains(htmlspecialchars($code), $document);
        $this->assertEquals(1, substr_count($document, '<code>'));
        $this->assertEquals(1, substr_count($document, '</code>'));
    }

    /**
     * Testing separators
     */
    public function testSeparator() : void
    {
        $document = $this->parseHTML('separator.rst');

        $this->assertContains('<hr />', $document);
    }

    /**
     * Testing the images feature
     */
    public function testImage() : void
    {
        $document = $this->parseHTMl('image.rst');

        $this->assertContains('<img', $document);
        $this->assertContains('src="test.jpg"', $document);
        $this->assertContains('src="try.jpg"', $document);
        $this->assertContains('src="other.jpg"', $document);
        $this->assertContains('width="123"', $document);
        $this->assertContains('title="Other"', $document);
        $this->assertNotContains('..', $document);
        $this->assertNotContains('image', $document);
        $this->assertNotContains('::', $document);

        $document = $this->parseHTML('image-inline.rst');

        $this->assertContains('<img', $document);
        $this->assertContains('src="test.jpg"', $document);
    }

    /**
     * Testing figure directive
     */
    public function testFigure() : void
    {
        $document = $this->parseHTMl('figure.rst');

        $this->assertContains('<figure>', $document);
        $this->assertContains('<img', $document);
        $this->assertContains('src="foo.jpg"', $document);
        $this->assertContains('width="100"', $document);
        $this->assertContains('<figcaption>', $document);
        $this->assertContains('This is a foo!', $document);
        $this->assertContains('</figcaption>', $document);
    }

    /**
     * Testing that an image that just directly follows some text works
     */
    public function testImageFollow() : void
    {
        $document = $this->parseHTML('image-follow.rst');

        $this->assertEquals(1, substr_count($document, '<img'));
        $this->assertEquals(1, substr_count($document, '"img/test.jpg"'));
    }

    /**
     * Testing a list
     */
    public function testList() : void
    {
        $document = $this->parseHTML('list.rst');

        $this->assertEquals(1, substr_count($document, '<ul>'));
        $this->assertEquals(1, substr_count($document, '</ul>'));
        $this->assertNotContains('<ol>', $document);
        $this->assertEquals(4, substr_count($document, '<li>'));
        $this->assertEquals(4, substr_count($document, '</li>'));
        $this->assertNotContains('*', $document);
        $this->assertContains('This is', $document);
        $this->assertContains('Last line', $document);

        $document = $this->parseHTML('indented-list.rst');

        $this->assertEquals(1, substr_count($document, '<ul>'));
        $this->assertEquals(1, substr_count($document, '</ul>'));
        $this->assertNotContains('<ol>', $document);
        $this->assertEquals(4, substr_count($document, '<li>'));
        $this->assertEquals(4, substr_count($document, '</li>'));
        $this->assertNotContains('*', $document);
        $this->assertContains('This is', $document);

        $document = $this->parseHTML('ordered.rst');

        $this->assertEquals(1, substr_count($document, '<ol>'));
        $this->assertEquals(1, substr_count($document, '</ol>'));
        $this->assertNotContains('<ul>', $document);
        $this->assertEquals(3, substr_count($document, '<li>'));
        $this->assertEquals(3, substr_count($document, '</li>'));
        $this->assertNotContains('.', $document);
        $this->assertContains('First item', $document);

        $document = $this->parseHTML('ordered2.rst');

        $this->assertEquals(1, substr_count($document, '<ol>'));
        $this->assertEquals(1, substr_count($document, '</ol>'));
        $this->assertNotContains('<ul>', $document);
        $this->assertEquals(3, substr_count($document, '<li>'));
        $this->assertEquals(3, substr_count($document, '</li>'));
        $this->assertNotContains('.', $document);
        $this->assertContains('First item', $document);

        $document = $this->parseHTML('list-empty.rst');
        $this->assertEquals(1, substr_count($document, '<ol>'));
        $this->assertEquals(1, substr_count($document, '</ol>'));
        $this->assertEquals(1, substr_count($document, '<ul>'));
        $this->assertEquals(1, substr_count($document, '</ul>'));
        $this->assertEquals(5, substr_count($document, '<li>'));
        $this->assertEquals(5, substr_count($document, '</li>'));
        $this->assertContains('<p>This is not in the list</p>', $document);

        $document = $this->parseHTML('list-dash.rst');
        $this->assertEquals(1, substr_count($document, '<ul>'));
        $this->assertEquals(1, substr_count($document, '</ul>'));
        $this->assertEquals(2, substr_count($document, '<li class="dash">'));
        $this->assertEquals(2, substr_count($document, '</li>'));

        $document = $this->parseHTML('list-alternate-syntax.rst');
        $this->assertEquals(1, substr_count($document, '<ul>'));
        $this->assertEquals(1, substr_count($document, '</ul>'));
        $this->assertEquals(3, substr_count($document, '<li class="dash">'));
        $this->assertEquals(3, substr_count($document, '</li>'));
    }

    public function testEmptyParagraph() : void
    {
        $document = $this->parseHTML('empty-p.rst');

        $this->assertNotContains('<p></p>', $document);
    }

    /**
     * Testing css stylesheet
     */
    public function testStylesheet() : void
    {
        $document = $this->parseHTML('css.rst');

        $this->assertContains('<link rel="stylesheet" type="text/css" href="style.css"', $document);
    }

    /**
     * Testing a title that follows a wrapping directive
     */
    public function testTitleFollowDirective() : void
    {
        $document = $this->parseHTML('directive-title.rst');

        $this->assertEquals(1, substr_count($document, '<div class="note'));
        $this->assertEquals(1, substr_count($document, '<h1>'));
        $this->assertEquals(1, substr_count($document, '</h1>'));
    }

    /**
     * Block quotes run a parse and thus can mess with environment, a bug was fixed
     * and this test avoid it to be reproduced
     */
    public function testQuoteResetTitles() : void
    {
        $document = $this->parseHTML('quote-title.rst');

        $this->assertEquals(1, substr_count($document, '<h1>Title</h1>'));
        $this->assertEquals(1, substr_count($document, '<h2>Another title</h2>'));
    }

    /**
     * Testing quote
     */
    public function testQuote() : void
    {
        $document = $this->parseHTML('quote.rst');

        $this->assertEquals(1, substr_count($document, '<blockquote>'));
        $this->assertContains('<p>', $document);
        $this->assertContains('</p>', $document);
        $this->assertEquals(1, substr_count($document, '</blockquote>'));

        $document = $this->parseHTML('quote2.rst');

        $this->assertEquals(1, substr_count($document, '<blockquote>'));
        $this->assertContains('<p>', $document);
        $this->assertContains('</p>', $document);
        $this->assertEquals(1, substr_count($document, '</blockquote>'));
        $this->assertEquals(1, substr_count($document, '<strong>'));
        $this->assertEquals(1, substr_count($document, '</strong>'));
        $this->assertNotContains('*', $document);

        $document = $this->parseHTML('quote3.rst');

        $this->assertEquals(1, substr_count($document, '<blockquote>'));
        $this->assertContains('<p>', $document);
        $this->assertContains('</p>', $document);
        $this->assertEquals(1, substr_count($document, '</blockquote>'));
        $this->assertEquals(1, substr_count($document, '<img'));
    }

    /**
     * Testing code blocks
     */
    public function testCode() : void
    {
        $document = $this->parseHTML('code.rst');

        $this->assertEquals(1, substr_count($document, '<pre>'));
        $this->assertEquals(1, substr_count($document, '</pre>'));
        $this->assertEquals(1, substr_count($document, '<code'));
        $this->assertEquals(1, substr_count($document, '</code>'));
        $this->assertContains('This is a code block', $document);
        $this->assertNotContains('::', $document);
        $this->assertNotContains('<br', $document);

        $document = $this->parseHTML('code-block.rst');

        $this->assertEquals(1, substr_count($document, '<pre>'));
        $this->assertEquals(1, substr_count($document, '</pre>'));
        $this->assertEquals(1, substr_count($document, '<code'));
        $this->assertEquals(1, substr_count($document, '</code>'));
        $code = 'cout << "Hello world!" << endl;';
        $this->assertContains(htmlspecialchars($code), $document);

        $document = $this->parseHTML('code-java.rst');

        $this->assertEquals(1, substr_count($document, '<pre>'));
        $this->assertEquals(1, substr_count($document, '</pre>'));
        $this->assertEquals(1, substr_count($document, '<code class="java"'));
        $this->assertEquals(1, substr_count($document, '</code>'));

        $document = $this->parseHTML('code-list.rst');

        $this->assertEquals(1, substr_count($document, '<pre>'));
        $this->assertEquals(1, substr_count($document, '</pre>'));
        $this->assertContains('*', $document);
    }

    /**
     * Testing titles
     */
    public function testTitles() : void
    {
        $document = $this->parseHTML('titles.rst');

        $this->assertEquals(1, substr_count($document, '<h1>'));
        $this->assertEquals(1, substr_count($document, '<h1>'));
        $this->assertEquals(2, substr_count($document, '<h2>'));
        $this->assertEquals(2, substr_count($document, '</h2>'));
        $this->assertEquals(4, substr_count($document, '<h3>'));
        $this->assertEquals(4, substr_count($document, '</h3>'));
        $this->assertContains('<a id="main-title"></a><h1>Main title</h1>', $document);
        $this->assertContains('<a id="first-subtitle"></a><h2>First subtitle</h2>', $document);
        $this->assertContains('<a id="first-subsubtitle"></a><h3>First subsubtitle</h3>', $document);
        $this->assertContains('<a id="second-subsubtitle"></a><h3>Second subsubtitle</h3>', $document);
        $this->assertContains('<a id="third-subsubtitle"></a><h3>Third subsubtitle</h3>', $document);
        $this->assertContains('<a id="fourth-subsubtitle"></a><h3>Fourth subsubtitle</h3>', $document);
        $this->assertNotContains('==', $document);
        $this->assertNotContains('--', $document);
        $this->assertNotContains('~~', $document);
    }

    public function testTitlesAuto() : void
    {
        $document = $this->parseHTML('titles-auto.rst');

        $this->assertEquals(1, substr_count($document, '<h1>'));
        $this->assertEquals(1, substr_count($document, '<h1>'));
        $this->assertEquals(2, substr_count($document, '<h2>'));
        $this->assertEquals(2, substr_count($document, '</h2>'));
        $this->assertEquals(4, substr_count($document, '<h3>'));
        $this->assertEquals(4, substr_count($document, '</h3>'));
        $this->assertContains('<a id="main-title"></a>', $document);
        $this->assertNotContains('==', $document);
        $this->assertNotContains('--', $document);
        $this->assertNotContains('~~', $document);
    }

    /**
     * Testing that a wrapper node can be at end of file
     */
    public function testWrapperNodeEnd() : void
    {
        $document = $this->parseHTML('wrap.rst');

        $this->assertEquals(1, substr_count($document, 'note'));
    }

    /**
     * Tests a variable used with a wrap sub directive
     */
    public function testVariableWrap() : void
    {
        $document = $this->parseHTML('variable-wrap.rst');

        $this->assertEquals(2, substr_count($document, 'note'));
        $this->assertEquals(2, substr_count($document, 'important'));
    }

    public function testReferenceUnderDirective() : void
    {
        $document = $this->parseHTML('reference-directive.rst');

        $this->assertEquals(1, substr_count($document, 'note'));
        $this->assertEquals(1, substr_count($document, 'unresolved'));
    }

    public function testReferenceMatchingIsntTooEager() : void
    {
        // Before, it would render
        // <p><code>:doc:`lorem</code><a href="https://consectetur.org"> and 249a92befe90adcd3bb404a91d4e1520a17a8b56` sit `amet</a></p>

        $this->assertSame(
            "<p><code>:doc:`lorem`</code> and <code>:code:`what`</code> sit <a href=\"https://consectetur.org\">amet</a></p>\n",
            $this->parse('no-eager-literals.rst')->render()
        );
    }

    public function testUnknownDirective() : void
    {
        try {
            $document = $this->parseHTML('unknown-directive.rst');
            $this->assertTrue(false, 'Unknown directive should raise an exception');
        } catch (Throwable $e) {
            $message = $e->getMessage();
            $this->assertContains('unknown-directive.rst', $message);
            $this->assertContains('line 2', $message);
        }
    }

    /**
     * Testing div directive
     */
    public function testDivDirective() : void
    {
        $document = $this->parseHTML('div.rst');

        $this->assertEquals(1, substr_count($document, '<div'));
        $this->assertEquals(1, substr_count($document, 'class="testing"'));
        $this->assertEquals(1, substr_count($document, 'Hello!'));
        $this->assertEquals(1, substr_count($document, '</div>'));
    }

    /**
     * Testing that comments starting by ... are not handled as comments
     */
    public function testCommentThree() : void
    {
        $document = $this->parseHTML('comment-3.rst');

        $this->assertEquals(1, substr_count($document, '... This is not a comment!'));
        $this->assertEquals(0, substr_count($document, 'This is a comment!'));
    }

    /**
     * Testing crlf
     */
    public function testCRLF() : void
    {
        $document = $this->parseHTML('crlf.rst');

        $this->assertEquals(1, substr_count($document, '<h1>'), 'CRLF should be supported');
    }

    /**
     * Testing that emphasis and span elements are evaluated in links
     */
    public function testLinkSpan() : void
    {
        $document = $this->parseHTML('link-span.rst');

        $this->assertEquals(1, substr_count($document, '<strong>'));
    }

    /**
     * Testing removing BOM
     */
    public function testBom() : void
    {
        $document = $this->parseHTML('bom.rst');
        $this->assertNotContains('Should be a comment', $document);
    }

    /**
     * Testing with a raw directive
     */
    public function testRaw() : void
    {
        $document = $this->parseHTML('raw.rst');
        $this->assertContains('<u>Underlined!</u>', $document);
    }

    public function testAnchors() : void
    {
        $document = $this->parseHTML('anchor.rst');

        $this->assertContains('<a id="anchors"></a><h1>Anchors</h1>', $document);
        $this->assertContains('<p><a href="#anchor-section">@Anchor Section</a></p>', $document);
        $this->assertContains('<a id="anchor-section"></a><h1>@Anchor Section</h1>', $document);
        $this->assertContains('<a id="anchors"></a><h1>Anchors</h1>', $document);
        $this->assertContains('<a id="lists"></a>', $document);
        $this->assertContains('<p><a href="#lists">go to lists</a></p>', $document);
    }

    /**
     * Helper function, parses a file and returns the document
     * produced by the parser
     */
    private function parse($file)
    {
        $directory   = __DIR__ . '/html/';
        $parser      = new Parser();
        $environment = $parser->getEnvironment();
        $environment->setCurrentDirectory($directory);

        return $parser->parseFile($directory . $file);
    }

    private function parseHTML($file)
    {
        return $this->parse($file)->renderDocument();
    }
}
