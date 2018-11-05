<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Document;
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

        self::assertContains('<a href="http://www.google.com/">', $document);
        self::assertContains('<a href="http://xkcd.com/">', $document);
        self::assertContains('<a href="http://something.com/">', $document);
        self::assertContains('<a href="http://anonymous.com/">', $document);
        self::assertContains('<a href="http://www.github.com/">', $document);
        self::assertContains('<a href="mailto:jane@example.com">mailto:jane@example.com</a>', $document);
        self::assertContains('<a href="news:comp.lang.php">news:comp.lang.php</a>', $document);
        self::assertContains('<a href="http://www.w3.org/Addressing/">http://www.w3.org/Addressing/</a>', $document);
        self::assertContains('<a href="ftp://foo.example.com/rfc/">ftp://foo.example.com/rfc/</a>', $document);
        self::assertContains('<a href="http://www.ics.uci.edu/pub/ietf/uri/historical.html#WARNING">http://www.ics.uci.edu/pub/ietf/uri/historical.html#WARNING</a>', $document);
        self::assertContains('under_score', $document);
        self::assertContains(' spacy', $document);
        self::assertNotContains(' ,', $document);
        self::assertNotContains('`', $document);
    }

    /**
     * Test that standalone hyperlinks are converted to links
     */
    public function testStandaloneHyperlinks() : void
    {
        $document = $this->parseHTML('standalone-hyperlinks.rst');

        self::assertContains('<a href="http://daringfireball.net/2010/07/improved_regex_for_matching_urls">http://daringfireball.net/2010/07/improved_regex_for_matching_urls</a>', $document);
        self::assertContains('<a href="https://daringfireball.net/misc/2010/07/url-matching-regex-test-data.text">https://daringfireball.net/misc/2010/07/url-matching-regex-test-data.text</a>', $document);
        self::assertContains('<a href="https://mathiasbynens.be/demo/url-regex">https://mathiasbynens.be/demo/url-regex</a>', $document);
        self::assertContains('<a href="http://foo.com/blah_blah">http://foo.com/blah_blah</a>', $document);
        self::assertContains('<a href="http://foo.com/blah_blah/">http://foo.com/blah_blah/</a>', $document);
        self::assertContains('(Something like <a href="http://foo.com/blah_blah">http://foo.com/blah_blah</a>)', $document);
        self::assertContains('<a href="http://foo.com/(something)?after=parens">http://foo.com/(something)?after=parens</a>', $document);
        self::assertContains('<a href="http://foo.com/blah_blah">http://foo.com/blah_blah</a>.', $document);
        self::assertContains('<a href="http://foo.com/blah_blah/">http://foo.com/blah_blah/</a>.', $document);
        self::assertContains('&lt;<a href="http://foo.com/blah_blah">http://foo.com/blah_blah</a>&gt;', $document);
        self::assertContains('&lt;<a href="http://foo.com/blah_blah/">http://foo.com/blah_blah/</a>&gt;', $document);
        self::assertContains('<a href="http://foo.com/blah_blah">http://foo.com/blah_blah</a>,', $document);
        self::assertContains('<a href="http://www.extinguishedscholar.com/wpglob/?p=364">http://www.extinguishedscholar.com/wpglob/?p=364</a>.', $document);
        self::assertContains('<a href="http://✪df.ws/1234">http://✪df.ws/1234</a>', $document);
        self::assertContains('<a href="rdar://1234">rdar://1234</a>', $document);
        self::assertContains('<a href="rdar:/1234">rdar:/1234</a>', $document);
        self::assertContains('<a href="x-yojimbo-item://6303E4C1-6A6E-45A6-AB9D-3A908F59AE0E">x-yojimbo-item://6303E4C1-6A6E-45A6-AB9D-3A908F59AE0E</a>', $document);
        self::assertContains('<a href="message://%3c330e7f840905021726r6a4ba78dkf1fd71420c1bf6ff@mail.gmail.com%3e">message://%3c330e7f840905021726r6a4ba78dkf1fd71420c1bf6ff@mail.gmail.com%3e</a>', $document);
        self::assertContains('<a href="http://➡.ws/䨹">http://➡.ws/䨹</a>', $document);
        self::assertContains('&lt;tag&gt;<a href="http://example.com">http://example.com</a>&lt;/tag&gt;', $document);
        self::assertContains('<a href="http://example.com/something?with,commas,in,url">http://example.com/something?with,commas,in,url</a>, but not at end', $document);
        self::assertContains('What about &lt;<a href="mailto:gruber@daringfireball.net?subject=TEST">mailto:gruber@daringfireball.net?subject=TEST</a>&gt; (including brokets).', $document);
        self::assertContains('<a href="mailto:name@example.com">mailto:name@example.com</a>', $document);
        self::assertContains('<a href="http://www.asianewsphoto.com/(S(neugxif4twuizg551ywh3f55))/Web_ENG/View_DetailPhoto.aspx?PicId=752">http://www.asianewsphoto.com/(S(neugxif4twuizg551ywh3f55))/Web_ENG/View_DetailPhoto.aspx?PicId=752</a>', $document);
        self::assertContains('<a href="http://www.asianewsphoto.com/(S(neugxif4twuizg551ywh3f55))">http://www.asianewsphoto.com/(S(neugxif4twuizg551ywh3f55))</a>', $document);
        self::assertContains('<a href="http://lcweb2.loc.gov/cgi-bin/query/h?pp/horyd:@field(NUMBER+@band(thc+5a46634))">http://lcweb2.loc.gov/cgi-bin/query/h?pp/horyd:@field(NUMBER+@band(thc+5a46634))</a>', $document);
        self::assertContains('<a href="http://www.example.com/wpstyle/?p=364">http://www.example.com/wpstyle/?p=364</a>', $document);
        self::assertContains('<a href="https://www.example.com/foo/?bar=baz&inga=42&quux">https://www.example.com/foo/?bar=baz&amp;inga=42&amp;quux</a>', $document);
        self::assertContains('<a href="http://userid:password@example.com:8080">http://userid:password@example.com:8080</a>', $document);
        self::assertContains('<a href="http://userid:password@example.com:8080/">http://userid:password@example.com:8080/</a>', $document);
        self::assertContains('<a href="http://userid@example.com">http://userid@example.com</a>', $document);
        self::assertContains('<a href="http://userid@example.com/">http://userid@example.com/</a>', $document);
        self::assertContains('<a href="http://userid@example.com:8080">http://userid@example.com:8080</a>', $document);
        self::assertContains('<a href="http://userid@example.com:8080/">http://userid@example.com:8080/</a>', $document);
        self::assertContains('<a href="http://userid:password@example.com">http://userid:password@example.com</a>', $document);
        self::assertContains('<a href="http://userid:password@example.com/">http://userid:password@example.com/</a>', $document);
        self::assertContains('<a href="http://142.42.1.1/">http://142.42.1.1/</a>', $document);
        self::assertContains('<a href="http://142.42.1.1:8080/">http://142.42.1.1:8080/</a>', $document);
        self::assertContains('<a href="http://⌘.ws">http://⌘.ws</a>', $document);
        self::assertContains('<a href="http://⌘.ws/">http://⌘.ws/</a>', $document);
        self::assertContains('<a href="http://foo.com/(something)?after=parens">http://foo.com/(something)?after=parens</a>', $document);
        self::assertContains('<a href="http://☺.damowmow.com/">http://☺.damowmow.com/</a>', $document);
        self::assertContains('<a href="http://code.google.com/events/#&product=browser">http://code.google.com/events/#&amp;product=browser</a>', $document);
        self::assertContains('<a href="http://j.mp">http://j.mp</a>', $document);
        self::assertContains('<a href="ftp://foo.bar/baz">ftp://foo.bar/baz</a>', $document);
        self::assertContains('<a href="http://foo.bar/?q=Test%20URL-encoded%20stuff">http://foo.bar/?q=Test%20URL-encoded%20stuff</a>', $document);
        self::assertContains('<a href="http://例子.测试">http://例子.测试</a>', $document);
        self::assertContains('<a href="http://उदाहरण.परीक्षा">http://उदाहरण.परीक्षा</a>', $document);
        self::assertContains('<a href="http://-._!$&\'()*+,;=:%40:80%2f::::::@example.com">http://-._!$&amp;\'()*+,;=:%40:80%2f::::::@example.com</a>', $document);
        self::assertContains('<a href="http://1337.net">http://1337.net</a>', $document);
        self::assertContains('<a href="http://a.b-c.de">http://a.b-c.de</a>', $document);
        self::assertContains('<a href="http://223.255.255.254">http://223.255.255.254</a>', $document);
        self::assertContains('<a href="mailto:jane.doe@example.com">mailto:jane.doe@example.com</a>', $document);
        self::assertContains('<a href="chrome://chrome">chrome://chrome</a>', $document);
        self::assertContains('<a href="irc://irc.freenode.net:6667/freenode">irc://irc.freenode.net:6667/freenode</a>', $document);
        self::assertContains('<a href="microsoft.windows.camera:foobar">microsoft.windows.camera:foobar</a>', $document);
        self::assertContains('<a href="coaps+ws://foobar">coaps+ws://foobar</a>', $document);
        self::assertContains('<a href="http://example.com/uris">http://example.com/uris</a>', $document);
        self::assertContains('<a href="mailto:same-line@example.com">mailto:same-line@example.com</a>', $document);

        self::assertNotContains('<a href="6:00p">', $document);
        self::assertNotContains('<a href="filename.txt">', $document);
        self::assertNotContains('<a href="www.c.ws/䨹">', $document);
        self::assertNotContains('<a href="www.example.com">', $document);
        self::assertNotContains('<a href="bit.ly/foo">', $document);
        self::assertNotContains('<a href="is.gd/foo/">', $document);
        self::assertNotContains('<a href="WWW.EXAMPLE.COM">', $document);
        self::assertNotContains('<a href="http://">', $document);
        self::assertNotContains('<a href="http://.">', $document);
        self::assertNotContains('<a href="http://..">', $document);
        self::assertNotContains('<a href="http://?">', $document);
        self::assertNotContains('<a href="http://??">', $document);
        self::assertNotContains('<a href="//">', $document);
        self::assertNotContains('<a href="//a">', $document);
        self::assertNotContains('<a href="///a">', $document);
        self::assertNotContains('<a href="///">', $document);
        self::assertNotContains('<a href="foo.com">', $document);
        self::assertNotContains('<a href="h://test">', $document);
        self::assertNotContains('<a href="http:// shouldfail.com">', $document);
        self::assertNotContains('<a href=":// should fail">', $document);
        self::assertNotContains('<a href="✪df.ws/1234">', $document);
        self::assertNotContains('<a href="example.com">', $document);
        self::assertNotContains('<a href="example.com/">', $document);
    }

    /**
     * Tests that hyperlinks that contain underscores are not mangled by the parser
     *
     * We are skipping these assertions for now because the parser is being
     * confused by the underscores (i.e. "blah_"), thinking that they are inline
     * named links.
     */
    public function testStandaloneHyperlinksWithUnderscores() : void
    {
        self::markTestSkipped('Skipping due to broken parsing of standalone hyperlinks.');

        $document = $this->parseHTML('standalone-hyperlinks.rst');

        self::assertContains('<a href="http://foo.com/blah_blah_(wikipedia)">http://foo.com/blah_blah_(wikipedia)</a>', $document);
        self::assertContains('<a href="http://foo.com/blah_blah_(wikipedia)_(again)">http://foo.com/blah_blah_(wikipedia)_(again)</a>', $document);
        self::assertContains('<a href="http://foo.com/more_(than)_one_(parens)">http://foo.com/more_(than)_one_(parens)</a>', $document);
        self::assertContains('<a href="http://foo.com/blah_(wikipedia)#cite-1">http://foo.com/blah_(wikipedia)#cite-1</a>', $document);
        self::assertContains('<a href="http://foo.com/blah_(wikipedia)_blah#cite-1">http://foo.com/blah_(wikipedia)_blah#cite-1</a>', $document);
        self::assertContains('<a href="http://foo.com/unicode_(✪)_in_parens">http://foo.com/unicode_(✪)_in_parens</a>', $document);
    }

    /**
     * Test that standalone email addresses are converted to links
     */
    public function testStandaloneEmailAddresses() : void
    {
        $document = $this->parseHTML('standalone-email-addresses.rst');

        self::assertContains('<a href="mailto:jdoe@machine1.example">jdoe@machine1.example</a>', $document);
        self::assertContains('<a href="mailto:mjones@machine1.example">mjones@machine1.example</a>', $document);
        self::assertContains('<a href="mailto:mary@example1.net">mary@example1.net</a>', $document);
        self::assertContains('<a href="mailto:1234@local.machine.example">1234@local.machine.example</a>', $document);
        self::assertContains('<a href="mailto:john.q.public@example.com">john.q.public@example.com</a>', $document);
        self::assertContains('<a href="mailto:mary@x.test">mary@x.test</a>', $document);
        self::assertContains('<a href="mailto:jdoe@example.org">jdoe@example.org</a>', $document);
        self::assertContains('<a href="mailto:one@y.test">one@y.test</a>', $document);
        self::assertContains('<a href="mailto:boss@nil.test">boss@nil.test</a>', $document);
        self::assertContains('<a href="mailto:sysservices@example.net">sysservices@example.net</a>', $document);
        self::assertContains('<a href="mailto:5678.21-Nov-1997@example.com">5678.21-Nov-1997@example.com</a>', $document);
        self::assertContains('<a href="mailto:pete@silly.example">pete@silly.example</a>', $document);
        self::assertContains('<a href="mailto:c@a.test">c@a.test</a>', $document);
        self::assertContains('<a href="mailto:joe@where.test">joe@where.test</a>', $document);
        self::assertContains('<a href="mailto:jdoe@one1.test">jdoe@one1.test</a>', $document);
        self::assertContains('<a href="mailto:testabcd.1234@silly.example">testabcd.1234@silly.example</a>', $document);
        self::assertContains('<a href="mailto:smith@home.example">smith@home.example</a>', $document);
        self::assertContains('<a href="mailto:jdoe@machine2.example">jdoe@machine2.example</a>', $document);
        self::assertContains('<a href="mailto:abcd.1234@local.machine.test">abcd.1234@local.machine.test</a>', $document);
        self::assertContains('<a href="mailto:3456@example.net">3456@example.net</a>', $document);
        self::assertContains('<a href="mailto:1235@local.machine.example">1235@local.machine.example</a>', $document);
        self::assertContains('<a href="mailto:3457@example.net">3457@example.net</a>', $document);
        self::assertContains('<a href="mailto:mary@example2.net">mary@example2.net</a>', $document);
        self::assertContains('<a href="mailto:j-brown@other.example">j-brown@other.example</a>', $document);
        self::assertContains('<a href="mailto:78910@example.net">78910@example.net</a>', $document);
        self::assertContains('<a href="mailto:jdoe@machine3.example">jdoe@machine3.example</a>', $document);
        self::assertContains('<a href="mailto:mary@example3.net">mary@example3.net</a>', $document);
        self::assertContains('<a href="mailto:1236@local.machine.example">1236@local.machine.example</a>', $document);
        self::assertContains('<a href="mailto:mary@example4.net">mary@example4.net</a>', $document);
        self::assertContains('<a href="mailto:jdoe@node.example">jdoe@node.example</a>', $document);
        self::assertContains('<a href="mailto:mary@example5.net">mary@example5.net</a>', $document);
        self::assertContains('<a href="mailto:1236@local.node.example">1236@local.node.example</a>', $document);
        self::assertContains('<a href="mailto:joe@example.org">joe@example.org</a>', $document);
        self::assertContains('<a href="mailto:jdoe@one2.test">jdoe@one2.test</a>', $document);
        self::assertContains('<a href="mailto:testabcd.1234@silly.test">testabcd.1234@silly.test</a>', $document);
        self::assertContains('<a href="mailto:foo+bar@test.email.address">foo+bar@test.email.address</a>', $document);
        self::assertContains('<a href="mailto:foo.bar-baz_quux@example-foo.quux">foo.bar-baz_quux@example-foo.quux</a>', $document);
        self::assertContains('<a href="mailto:foo%20bar@example.testareallylongtldinanemailaddress">foo%20bar@example.testareallylongtldinanemailaddress</a>', $document);

        self::assertNotContains('<a href="mailto:pete(his account)@silly.test(his host)">', $document);
        self::assertNotContains('<a href="mailto:c@(Chris\'s host.)public.example">', $document);
        self::assertNotContains('<a href="mailto:foo@example.">', $document);
        self::assertNotContains('<a href="mailto:foo@example">', $document);
        self::assertNotContains('<a href="mailto:@example.com">', $document);
        self::assertNotContains('<a href="mailto:@example">', $document);
        self::assertNotContains('<a href="mailto:foo.bar-baz_quux@example-foo_bar.quux">', $document);
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

        self::assertSame(1, substr_count($document, '<table class="table table-bordered">'));
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

        self::assertSame(1, substr_count($document, '<table class="table table-bordered">'));
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
        self::assertSame(2, substr_count($document, '<h2>'));
        self::assertSame(2, substr_count($document, '</h2>'));
        self::assertSame(4, substr_count($document, '<h3>'));
        self::assertSame(4, substr_count($document, '</h3>'));
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
