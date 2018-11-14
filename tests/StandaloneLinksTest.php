<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Nodes\DocumentNode;
use Doctrine\RST\Parser;
use PHPUnit\Framework\TestCase;

class StandaloneLinksTest extends TestCase
{
    /**
     * Test some links demo
     */
    public function testLinks() : void
    {
        $document = $this->parseHTML('links.rst');

        self::assertContains('<a href="mailto:jane@example.com">mailto:jane@example.com</a>', $document);
        self::assertContains('<a href="news:comp.lang.php">news:comp.lang.php</a>', $document);
        self::assertContains('<a href="http://www.w3.org/Addressing/">http://www.w3.org/Addressing/</a>', $document);
        self::assertContains('<a href="ftp://foo.example.com/rfc/">ftp://foo.example.com/rfc/</a>', $document);
        self::assertContains('<a href="http://www.ics.uci.edu/pub/ietf/uri/historical.html#WARNING">http://www.ics.uci.edu/pub/ietf/uri/historical.html#WARNING</a>', $document);
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
        $document = $this->parseHTML('standalone-hyperlinks.rst');

        self::assertContains('<a href="http://foo.com/blah_blah_(wikipedia)">http://foo.com/blah_blah_(wikipedia)</a>', $document);
        self::assertContains('(Something like <a href="http://foo.com/blah_blah_(wikipedia)">http://foo.com/blah_blah_(wikipedia)</a>)', $document);
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
     * Helper function, parses a file and returns the document
     * produced by the parser
     */
    private function parse(string $file) : DocumentNode
    {
        $directory   = __DIR__ . '/HTML/files/';
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
