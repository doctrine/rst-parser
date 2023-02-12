<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\Builder;

use Doctrine\RST\Builder;
use Doctrine\RST\Configuration;
use Doctrine\RST\Event\PostBuilderInitEvent;
use Doctrine\Tests\RST\BaseBuilderTest;
use Symfony\Component\DomCrawler\Crawler;

use function array_map;
use function array_unique;
use function array_values;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function iterator_to_array;
use function range;
use function sleep;
use function sprintf;
use function str_replace;
use function substr_count;
use function unserialize;

/**
 * Unit testing for RST
 */
class BuilderTest extends BaseBuilderTest
{
    public function testPostBuilderInitEventIsDispatched(): void
    {
        $configuration = $this->createMock(Configuration::class);
        $configuration->expects(self::once())
            ->method('dispatchEvent')
            ->with(PostBuilderInitEvent::POST_BUILDER_INIT);
        new Builder($configuration);
    }

    public function testRecreate(): void
    {
        $builder = $this->builder->recreate();

        self::assertSame($this->builder->getConfiguration(), $builder->getConfiguration());
    }

    /**
     * Tests that the build produced the excepted documents
     */
    public function testBuild(): void
    {
        self::assertTrue(is_dir($this->targetFile()));
        self::assertTrue(file_exists($this->targetFile('index.html')));
        self::assertTrue(file_exists($this->targetFile('introduction.html')));
        self::assertTrue(file_exists($this->targetFile('subdirective.html')));
        self::assertTrue(file_exists($this->targetFile('magic-link.html')));
        self::assertTrue(file_exists($this->targetFile('subdir/test.html')));
        self::assertTrue(file_exists($this->targetFile('subdir/file.html')));
    }

    public function testCachedMetas(): void
    {
        // check that metas were cached
        self::assertTrue(file_exists($this->targetFile('metas.php')));
        $cachedContents = (string) file_get_contents($this->targetFile('metas.php'));
        $metas          = unserialize($cachedContents);
        self::assertArrayHasKey('entries', $metas);
        $metaEntries = $metas['entries'];
        self::assertArrayHasKey('index', $metaEntries);
        self::assertSame('Summary', $metaEntries['index']->getTitle());
        self::assertArrayHasKey('linkTargets', $metas);
        $linkTargets = $metas['linkTargets'];
        self::assertArrayHasKey('toc', $linkTargets);
        self::assertSame('toc', $linkTargets['toc']->getName());

        // look at all the other documents this document depends
        // on, like :doc: and :ref:
        self::assertSame([
            'index',
            'toc-glob',
            'subdir/index',
        ], array_values(array_unique($metaEntries['introduction']->getDepends())));

        // assert the self-refs don't mess up dependencies
        self::assertSame([
            'subdir/index',
            'index',
            'subdir/file',
        ], array_values(array_unique($metaEntries['subdir/index']->getDepends())));

        // update meta cache to see that it was used
        // Summary is the main header in "index.rst"
        // we reference it in link-to-index.rst
        // it should cause link-to-index.rst to re-render with the new
        // title as the link
        file_put_contents(
            $this->targetFile('metas.php'),
            str_replace('Summary', 'Sumario', $cachedContents)
        );

        // also we need to trigger the link-to-index.rst as looking updated
        sleep(1);
        $contents = file_get_contents(__DIR__ . '/input/link-to-index.rst');
        file_put_contents(
            __DIR__ . '/input/link-to-index.rst',
            $contents . ' '
        );
        // change it back
        file_put_contents(
            __DIR__ . '/input/link-to-index.rst',
            $contents
        );

        // new builder, which will use cached metas
        $builder = new Builder(new Configuration());
        $builder->build($this->sourceFile(), $this->targetFile());

        $contents = $this->getFileContents($this->targetFile('link-to-index.html'));
        self::assertStringContainsString('Sumario', $contents);
    }

    /**
     * Tests the ..url :: directive
     */
    public function testUrl(): void
    {
        $contents = $this->getFileContents($this->targetFile('index.html'));

        self::assertStringContainsString('"magic-link.html', $contents);
        self::assertStringContainsString('Another page', $contents);
    }

    /**
     * Tests the links
     */
    public function testLinks(): void
    {
        $contents = $this->getFileContents($this->targetFile('subdir/test.html'));

        self::assertStringContainsString('"../to/resource"', $contents);
        self::assertStringContainsString('"http://absolute/"', $contents);

        self::assertStringContainsString('"http://google.com"', $contents);
        self::assertStringContainsString('"http://yahoo.com"', $contents);

        self::assertSame(2, substr_count($contents, 'http://something.com'));
    }

    public function testAnchor(): void
    {
        $contents = $this->getFileContents($this->targetFile('subdir/test.html'));

        self::assertStringContainsString('<p>This is a <a href="test.html#test-anchor">test anchor</a></p>', $contents);
        self::assertStringContainsString('<a id="test-anchor"></a>', $contents);
    }

    /**
     * Tests that the index toctree worked
     */
    public function testToctree(): void
    {
        $contents = $this->getFileContents($this->targetFile('index.html'));

        self::assertStringContainsString('"introduction.html', $contents);
        self::assertStringContainsString('Introduction page', $contents);

        self::assertStringContainsString('"subdirective.html', $contents);
        self::assertStringContainsString('"subdir/test.html', $contents);
        self::assertStringContainsString('"subdir/file.html', $contents);
    }

    public function testToctreeGlob(): void
    {
        $contents = $this->getFileContents($this->targetFile('toc-glob.html'));

        // links to first <h1> tag of other pages must not contain a url fragment
        self::assertStringContainsString('magic-link.html"', $contents);
        self::assertStringContainsString('introduction.html"', $contents);
        self::assertStringContainsString('subdirective.html"', $contents);
        self::assertStringContainsString('subdir/file.html"', $contents);

        // links to other <h1> tags should contain a url fragment
        self::assertStringContainsString('index.html#another-h1', $contents);

        // links to other headings contain a url fragment
        self::assertStringContainsString('subdir/test.html#subdirectory', $contents);
        self::assertStringContainsString('subdir/file.html#heading-2', $contents);

        // link to <h1> inside the same page contains a url fragment
        self::assertStringContainsString('toc-glob.html#toc-glob', $contents);
    }

    public function testToctreeGlobOrder(): void
    {
        $contents = $this->getFileContents($this->targetFile('toc-glob.html'));

        // assert `index` is first since it is defined first in toc-glob.rst
        self::assertStringContainsString('<div class="toc"><ul><li id="index-html" class="toc-item"><a href="index.html">Summary</a></li>', $contents);

        // assert `index` is not included and duplicated by the glob
        self::assertStringNotContainsString('</ul><li id="index-html" class="toc-item"><a href="index.html">Summary</a></li>', $contents);

        // assert `introduction` is at the end after the glob since it is defined last in toc-glob.rst
        self::assertStringContainsString('<a href="introduction.html">Introduction page</a></li></ul></div>', $contents);

        // assert the glob part is alphabetical
        $crawler       = new Crawler($contents);
        $expectedLinks = [
            'index.html',
            // a second "h1" (a rare thing) is included as a top-level headline
            'index.html#another-h1',
            // this is another.rst - it has a custom url
            'magic-link.html',
            'introduction.html',
            'link-to-index.html',
            // the subdir handling is actually different than Sphinx, which
            // does not look into subdirs with a normal * glob
            'subdir/file.html',
            'subdir/test.html',
            'subdir/toc.html',
            'subdirective.html',
            'toc-glob-reversed.html',
            // only here because we explicitly include it, "self file" is normally ignored
            'toc-glob.html#toc-glob',
            // this is manually included again
            'introduction.html',
        ];
        $actualLinks   = array_map(static fn ($linkElement): string => $linkElement->attributes->getNamedItem('href')->nodeValue, iterator_to_array($crawler->filter('.toc > ul > li > a')));
        self::assertSame($expectedLinks, $actualLinks);
    }

    public function testToctreeGlobReversedOrder(): void
    {
        $contents = $this->getFileContents($this->targetFile('toc-glob-reversed.html'));

        $crawler = new Crawler($contents);
        // see previous test for why they are in this order (now reversed)
        $expectedLinks = [
            'introduction.html',
            'toc-glob.html',
            'subdirective.html',
            'subdir/toc.html',
            'subdir/test.html',
            'subdir/file.html',
            'link-to-index.html',
            'introduction.html',
            'magic-link.html',
            'index.html',
            // having the other h1 anchor AFTER index.html is what Sphinx does too
            'index.html#another-h1',
        ];
        $actualLinks   = array_map(static fn ($linkElement): string => $linkElement->attributes->getNamedItem('href')->nodeValue, iterator_to_array($crawler->filter('.toc > ul > li > a')));
        self::assertSame($expectedLinks, $actualLinks);
    }

    public function testToctreeInSubdirectory(): void
    {
        $contents = $this->getFileContents($this->targetFile('subdir/toc.html'));

        self::assertStringContainsString('../introduction.html"', $contents);
        self::assertStringContainsString('../subdirective.html"', $contents);
        self::assertStringContainsString('../magic-link.html"', $contents);
        self::assertStringContainsString('"test.html"', $contents);
        self::assertStringContainsString('file.html"', $contents);
    }

    public function testAnchors(): void
    {
        $contents = $this->getFileContents($this->targetFile('index.html'));

        self::assertStringContainsString('<a id="reference_anchor"></a>', $contents);

        $contents = $this->getFileContents($this->targetFile('introduction.html'));

        self::assertStringContainsString('<p>Reference to the <a href="index.html#reference_anchor">Summary Reference</a></p>', $contents);
    }

    /**
     * Testing references to other documents
     */
    public function testReferences(): void
    {
        $contents = $this->getFileContents($this->targetFile('introduction.html'));

        self::assertStringContainsString('<a href="index.html#toc">Index, paragraph toc</a>', $contents);
        self::assertStringContainsString('<a href="index.html">Index</a>', $contents);
        self::assertStringContainsString('<a href="index.html">Summary</a>', $contents);
        self::assertStringContainsString('<a href="index.html">Link index absolutely</a>', $contents);
        self::assertStringContainsString('<a href="subdir/test.html#test_reference">Test Reference</a>', $contents);
        self::assertStringContainsString('<a href="subdir/test.html#camelCaseReference">Camel Case Reference</a>', $contents);

        $contents = $this->getFileContents($this->targetFile('subdir/test.html'));

        self::assertStringContainsString('"../index.html"', $contents);
        self::assertStringContainsString('<a href="test.html#subdir_same_doc_reference">the subdir same doc reference</a>', $contents);
        self::assertStringContainsString('<a href="../index.html">Reference absolute to index</a>', $contents);
        self::assertStringContainsString('<a href="file.html">Reference absolute to file</a>', $contents);
        self::assertStringContainsString('<a href="file.html">Reference relative to file</a>', $contents);

        $contents = $this->getFileContents($this->targetFile('index.html'));

        self::assertStringContainsString('Link to <a href="index.html#same_doc_reference">the same doc reference</a>', $contents);
        self::assertStringContainsString('Link to <a href="index.html#same_doc_reference_ticks">the same doc reference with ticks</a>', $contents);

        self::assertStringContainsString('Link to <a href="subdir/test.html#subdirectory">Subdirectory</a>', $contents);
        self::assertStringContainsString('Link to <a href="subdir/test.html#subdirectory">Subdirectory Test</a>', $contents);

        self::assertStringContainsString('Link to <a href="subdir/test.html#subdirectory-child">Subdirectory Child', $contents);
        self::assertStringContainsString('Link to <a href="subdir/test.html#subdirectory-child">Subdirectory Child Test</a>', $contents);

        self::assertStringContainsString('Link to <a href="subdir/test.html#subdirectory-child-level-2">Subdirectory Child Level 2', $contents);
        self::assertStringContainsString('Link to <a href="subdir/test.html#subdirectory-child-level-2">Subdirectory Child Level 2 Test</a>', $contents);

        self::assertStringContainsString('Link to <a href="subdir/test.html#subdirectory-child-level-3">Subdirectory Child Level 3', $contents);
        self::assertStringContainsString('Link to <a href="subdir/test.html#subdirectory-child-level-3">Subdirectory Child Level 3 Test</a>', $contents);
    }

    public function testSubdirReferences(): void
    {
        $contents = $this->getFileContents($this->targetFile('subdir/test.html'));

        self::assertStringContainsString('<p>This is a <a href="test.html#test-anchor">test anchor</a></p>', $contents);
        self::assertStringContainsString('<p>This is a <a href="test.html#test-subdir-anchor">test subdir reference with anchor</a></p>', $contents);
    }

    public function testFileInclude(): void
    {
        $contents = $this->getFileContents($this->targetFile('subdir/test.html'));
        self::assertSame(2, substr_count($contents, 'This file is included'));
    }

    /**
     * Testing wrapping sub directive
     */
    public function testSubDirective(): void
    {
        $contents = $this->getFileContents($this->targetFile('subdirective.html'));

        self::assertSame(2, substr_count($contents, '<div class="note">'));
        self::assertSame(2, substr_count($contents, '<li>'));
        self::assertStringContainsString('</div>', $contents);
        self::assertSame(2, substr_count($contents, '</li>'));
        self::assertSame(1, substr_count($contents, '<ul>'));
        self::assertSame(1, substr_count($contents, '</ul>'));
        self::assertStringContainsString('<p>This is a simple note!</p>', $contents);
        self::assertStringContainsString('<h2>There is a title here</h2>', $contents);
    }

    public function testReferenceInDirective(): void
    {
        $contents = $this->getFileContents($this->targetFile('index.html'));

        self::assertStringContainsString(
            '<div class="note"><p><a href="introduction.html">Reference in directory</a></p>',
            $contents
        );
    }

    public function testTitleLinks(): void
    {
        $contents = $this->getFileContents($this->targetFile('magic-link.html'));

        self::assertStringContainsString(
            '<p>see <a href="magic-link.html#see-also">See also</a></p>',
            $contents
        );

        self::assertStringContainsString(
            '<p>see <a href="magic-link.html#another-page">Another page</a></p>',
            $contents
        );

        self::assertStringContainsString(
            '<p>see <a href="http://absolute/">test</a></p>',
            $contents
        );

        self::assertStringContainsString(
            '<p>see <a href="magic-link.html#title-with-ampersand">title with ampersand &amp;</a></p>',
            $contents
        );

        self::assertStringContainsString(
            '<p>see <a href="magic-link.html#a-title-with-ticks">A title with ticks</a></p>',
            $contents
        );
    }

    public function testHeadings(): void
    {
        $contents = $this->getFileContents($this->targetFile('subdir/file.html'));

        foreach (range(1, 6) as $index) {
            self::assertStringContainsString(
                sprintf(
                    '<h%d>Heading %d</h%d>',
                    $index,
                    $index,
                    $index
                ),
                $contents
            );
        }
    }

    public function testReferenceToTitleWith2CharactersLong(): void
    {
        $contents = $this->getFileContents($this->targetFile('subdir/test.html'));

        self::assertStringContainsString(
            '<a href="test.html#em">em</a>',
            $contents
        );
    }

    public function testDocumentRoot(): void
    {
        $root = $this->builder->getMetas()->getDocumentRoot();
        self::assertNotNull($root);
        self::assertEquals('index', $root->getFile());
        self::assertTrue($root->isDocumentRoot());
        self::assertNull($root->getParentDocument());
    }

    public function testDocumentRootChildren(): void
    {
        $root     = $this->builder->getMetas()->getDocumentRoot();
        $children = $root->getChildDocuments();
        self::assertCount(5, $children);
    }

    protected function getFixturesDirectory(): string
    {
        return 'Builder';
    }
}
