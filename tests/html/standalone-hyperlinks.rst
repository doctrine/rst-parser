Test data for the URL-matching regex pattern presented here:

http://daringfireball.net/2010/07/improved_regex_for_matching_urls

This list of URLs is adapted from
https://daringfireball.net/misc/2010/07/url-matching-regex-test-data.text and
https://mathiasbynens.be/demo/url-regex.


Matches the right thing in the following lines:

    http://foo.com/blah_blah
    http://foo.com/blah_blah/
    (Something like http://foo.com/blah_blah)
    http://foo.com/(something)?after=parens
    http://foo.com/blah_blah.
    http://foo.com/blah_blah/.
    <http://foo.com/blah_blah>
    <http://foo.com/blah_blah/>
    http://foo.com/blah_blah,
    http://www.extinguishedscholar.com/wpglob/?p=364.
    http://✪df.ws/1234
    rdar://1234
    rdar:/1234
    x-yojimbo-item://6303E4C1-6A6E-45A6-AB9D-3A908F59AE0E
    message://%3c330e7f840905021726r6a4ba78dkf1fd71420c1bf6ff@mail.gmail.com%3e
    http://➡.ws/䨹
    <tag>http://example.com</tag>
    http://example.com/something?with,commas,in,url, but not at end
    What about <mailto:gruber@daringfireball.net?subject=TEST> (including brokets).
    mailto:name@example.com
    http://www.asianewsphoto.com/(S(neugxif4twuizg551ywh3f55))/Web_ENG/View_DetailPhoto.aspx?PicId=752
    http://www.asianewsphoto.com/(S(neugxif4twuizg551ywh3f55))
    http://lcweb2.loc.gov/cgi-bin/query/h?pp/horyd:@field(NUMBER+@band(thc+5a46634))
    http://www.example.com/wpstyle/?p=364
    https://www.example.com/foo/?bar=baz&inga=42&quux
    http://userid:password@example.com:8080
    http://userid:password@example.com:8080/
    http://userid@example.com
    http://userid@example.com/
    http://userid@example.com:8080
    http://userid@example.com:8080/
    http://userid:password@example.com
    http://userid:password@example.com/
    http://142.42.1.1/
    http://142.42.1.1:8080/
    http://⌘.ws
    http://⌘.ws/
    http://☺.damowmow.com/
    http://code.google.com/events/#&product=browser
    http://j.mp
    ftp://foo.bar/baz
    http://foo.bar/?q=Test%20URL-encoded%20stuff
    http://例子.测试
    http://उदाहरण.परीक्षा
    http://-._!$&'()*+,;=:%40:80%2f::::::@example.com
    http://1337.net
    http://a.b-c.de
    http://223.255.255.254
    mailto:jane.doe@example.com
    chrome://chrome
    irc://irc.freenode.net:6667/freenode
    microsoft.windows.camera:foobar
    coaps+ws://foobar
    How about multiple http://example.com/uris on the mailto:same-line@example.com


Should fail against:
    6:00p
    filename.txt
    www.c.ws/䨹
    Just a www.example.com link.
    bit.ly/foo
    “is.gd/foo/”
    WWW.EXAMPLE.COM
    http://
    http://.
    http://..
    http://?
    http://??
    //
    //a
    ///
    foo.com
    h://test
    http:// shouldfail.com
    :// should fail
    ✪df.ws/1234
    example.com
    example.com/


These are currently problematic and fail, since the parser appears to see named
links appearing within these URLs (i.e. "blah_") and attempts to replace these
with tokens. These should match the standalone hyperlink pattern, but they do
not at this time.

    http://foo.com/blah_blah_(wikipedia)
    (Something like http://foo.com/blah_blah_(wikipedia))
    http://foo.com/more_(than)_one_(parens)
    http://foo.com/blah_(wikipedia)#cite-1
    http://foo.com/blah_(wikipedia)_blah#cite-1
    http://foo.com/unicode_(✪)_in_parens
