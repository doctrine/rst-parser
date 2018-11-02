<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use function file_exists;
use function is_dir;
use function substr_count;

/**
 * Unit testing for RST
 */
class BuilderTest extends BaseBuilderTest
{
    /**
     * Tests that the build produced the excepted documents
     */
    public function testBuild() : void
    {
        self::assertTrue(is_dir($this->targetFile()));
        self::assertTrue(file_exists($this->targetFile('index.html')));
        self::assertTrue(file_exists($this->targetFile('introduction.html')));
        self::assertTrue(file_exists($this->targetFile('subdirective.html')));
        self::assertTrue(file_exists($this->targetFile('magic-link.html')));
        self::assertTrue(file_exists($this->targetFile('subdir/test.html')));
        self::assertTrue(file_exists($this->targetFile('subdir/file.html')));
    }

    /**
     * Tests the ..url :: directive
     */
    public function testUrl() : void
    {
        $contents = $this->getFileContents($this->targetFile('index.html'));

        self::assertContains('"magic-link.html', $contents);
        self::assertContains('Another page', $contents);
    }

    /**
     * Tests the links
     */
    public function testLinks() : void
    {
        $contents = $this->getFileContents($this->targetFile('subdir/test.html'));

        self::assertContains('"../to/resource"', $contents);
        self::assertContains('"http://absolute/"', $contents);

        self::assertContains('"http://google.com"', $contents);
        self::assertContains('"http://yahoo.com"', $contents);

        self::assertSame(2, substr_count($contents, 'http://something.com'));
    }

    public function testAnchor() : void
    {
        $contents = $this->getFileContents($this->targetFile('subdir/test.html'));

        self::assertContains('<p>This is a <a href="test.html#test-anchor">test anchor</a></p>', $contents);
        self::assertContains('<a id="test-anchor"></a>', $contents);
    }

    /**
     * Tests that the index toctree worked
     */
    public function testToctree() : void
    {
        $contents = $this->getFileContents($this->targetFile('index.html'));

        self::assertContains('"introduction.html', $contents);
        self::assertContains('Introduction page', $contents);

        self::assertContains('"subdirective.html', $contents);
        self::assertContains('"subdir/test.html', $contents);
        self::assertContains('"subdir/file.html', $contents);
    }

    public function testToctreeGlob() : void
    {
        $contents = $this->getFileContents($this->targetFile('toc-glob.html'));

        self::assertContains('magic-link.html#another-page', $contents);
        self::assertContains('introduction.html#introduction-page', $contents);
        self::assertContains('subdirective.html', $contents);
        self::assertContains('subdir/test.html#subdirectory', $contents);
        self::assertContains('subdir/file.html#a-file', $contents);
    }

    public function testToctreeGlobOrder() : void
    {
        $contents = $this->getFileContents($this->targetFile('toc-glob.html'));

        // assert `index` is first since it is defined first in toc-glob.rst
        self::assertContains('<div class="toc"><ul><li id="index-html-summary" class="toc-item"><a href="index.html#summary">Summary</a></li>', $contents);

        // assert `index` is not included and duplicated by the glob
        self::assertNotContains('</ul><li id="index-html-summary" class="toc-item"><a href="index.html#summary">Summary</a></li>', $contents);

        // assert `introduction` is at the end after the glob since it is defined last in toc-glob.rst
        self::assertContains('<a href="introduction.html#introduction-page">Introduction page</a></li></ul></div>', $contents);
    }

    public function testToctreeInSubdirectory() : void
    {
        $contents = $this->getFileContents($this->targetFile('subdir/toc.html'));

        self::assertContains('../introduction.html#introduction-page', $contents);
        self::assertContains('../subdirective.html#sub-directives', $contents);
        self::assertContains('../magic-link.html#another-page', $contents);
        self::assertContains('test.html#subdirectory', $contents);
        self::assertContains('file.html#a-file', $contents);
    }

    public function testAnchors() : void
    {
        $contents = $this->getFileContents($this->targetFile('index.html'));

        self::assertContains('<a id="reference_anchor"></a>', $contents);

        $contents = $this->getFileContents($this->targetFile('introduction.html'));

        self::assertContains('<p>Reference to the <a href="index.html#reference_anchor">Summary Reference</a></p>', $contents);
    }

    /**
     * Testing references to other documents
     */
    public function testReferences() : void
    {
        $contents = $this->getFileContents($this->targetFile('introduction.html'));

        self::assertContains('<a href="index.html#toc">Index, paragraph toc</a>', $contents);
        self::assertContains('<a href="index.html">Index</a>', $contents);
        self::assertContains('<a href="index.html">Summary</a>', $contents);
        self::assertContains('<a href="index.html">Link index absolutely</a>', $contents);
        self::assertContains('<a href="subdir/test.html#test_reference">Test Reference</a>', $contents);
        self::assertContains('<a href="subdir/test.html#camelCaseReference">Camel Case Reference</a>', $contents);

        $contents = $this->getFileContents($this->targetFile('subdir/test.html'));

        self::assertContains('"../index.html"', $contents);
        self::assertContains('<a href="test.html#subdir_same_doc_reference">the subdir same doc reference</a>', $contents);
        self::assertContains('<a href="../index.html">Reference absolute to index</a>', $contents);
        self::assertContains('<a href="file.html">Reference absolute to file</a>', $contents);
        self::assertContains('<a href="file.html">Reference relative to file</a>', $contents);

        $contents = $this->getFileContents($this->targetFile('index.html'));

        self::assertContains('Link to <a href="subdir/test.html#subdirectory">Subdirectory</a>', $contents);
        self::assertContains('Link to <a href="subdir/test.html#subdirectory">Subdirectory Test</a>', $contents);

        self::assertContains('Link to <a href="subdir/test.html#subdirectory-child">Subdirectory Child', $contents);
        self::assertContains('Link to <a href="subdir/test.html#subdirectory-child">Subdirectory Child Test</a>', $contents);

        self::assertContains('Link to <a href="subdir/test.html#subdirectory-child-level-2">Subdirectory Child Level 2', $contents);
        self::assertContains('Link to <a href="subdir/test.html#subdirectory-child-level-2">Subdirectory Child Level 2 Test</a>', $contents);

        self::assertContains('Link to <a href="subdir/test.html#subdirectory-child-level-3">Subdirectory Child Level 3', $contents);
        self::assertContains('Link to <a href="subdir/test.html#subdirectory-child-level-3">Subdirectory Child Level 3 Test</a>', $contents);
    }

    public function testSubdirReferences() : void
    {
        $contents = $this->getFileContents($this->targetFile('subdir/test.html'));

        self::assertContains('<p>This is a <a href="test.html#test-anchor">test anchor</a></p>', $contents);
        self::assertContains('<p>This is a <a href="test.html#test-subdir-anchor">test subdir reference with anchor</a></p>', $contents);
    }

    public function testFileInclude() : void
    {
        $contents = $this->getFileContents($this->targetFile('subdir/test.html'));
        self::assertSame(2, substr_count($contents, 'This file is included'));
    }

    /**
     * Testing wrapping sub directive
     */
    public function testSubDirective() : void
    {
        $contents = $this->getFileContents($this->targetFile('subdirective.html'));

        self::assertSame(2, substr_count($contents, '<div class="note">'));
        self::assertSame(2, substr_count($contents, '<li>'));
        self::assertContains('</div>', $contents);
        self::assertSame(2, substr_count($contents, '</li>'));
        self::assertSame(1, substr_count($contents, '<ul>'));
        self::assertSame(1, substr_count($contents, '</ul>'));
        self::assertContains('<p>This is a simple note!</p>', $contents);
        self::assertContains('<h2>There is a title here</h2>', $contents);
    }

    public function testReferenceInDirective() : void
    {
        $contents = $this->getFileContents($this->targetFile('index.html'));

        self::assertContains(
            '<div class="note"><p><a href="introduction.html">Reference in directory</a></p>',
            $contents
        );
    }

    protected function getFixturesDirectory() : string
    {
        return 'builder-fixtures';
    }
}
