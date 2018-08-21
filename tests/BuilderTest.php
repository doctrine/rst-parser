<?php

declare(strict_types=1);

use Doctrine\RST\Builder;
use PHPUnit\Framework\TestCase;

/**
 * Unit testing for RST
 */
class BuilderTest extends TestCase
{
    /**
     * Tests that the build produced the excepted documents
     */
    public function testBuild() : void
    {
        $this->assertTrue(is_dir($this->targetFile()));
        $this->assertTrue(file_exists($this->targetFile('index.html')));
        $this->assertTrue(file_exists($this->targetFile('introduction.html')));
        $this->assertTrue(file_exists($this->targetFile('subdirective.html')));
        $this->assertTrue(file_exists($this->targetFile('magic-link.html')));
        $this->assertTrue(file_exists($this->targetFile('file.txt')));
        $this->assertTrue(file_exists($this->targetFile('subdir/test.html')));
    }

    /**
     * Tests the ..url :: directive
     */
    public function testUrl() : void
    {
        $contents = file_get_contents($this->targetFile('index.html'));

        $this->assertContains('"magic-link.html', $contents);
        $this->assertContains('Another page', $contents);
    }

    /**
     * Tests the links
     */
    public function testLinks() : void
    {
        $contents = file_get_contents($this->targetFile('subdir/test.html'));

        $this->assertContains('"../to/resource"', $contents);
        $this->assertContains('"http://absolute/"', $contents);

        $this->assertContains('"http://google.com"', $contents);
        $this->assertContains('"http://yahoo.com"', $contents);

        $this->assertEquals(2, substr_count($contents, 'http://something.com'));
    }

    /**
     * Tests that the index toctree worked
     */
    public function testToctree() : void
    {
        $contents = file_get_contents($this->targetFile('index.html'));

        $this->assertContains('introduction.html', $contents);
        $this->assertContains('Introduction page', $contents);
    }

    public function testToctreeGlob() : void
    {
        $contents = file_get_contents($this->targetFile('toc-glob.html'));

        $this->assertContains('magic-link.html#another-page', $contents);
        $this->assertContains('introduction.html#introduction-page', $contents);
        $this->assertContains('subdirective.html', $contents);
        $this->assertContains('subdir/test.html#subdirectory', $contents);
    }

    public function testToctreeInSubdirectory() : void
    {
        $contents = file_get_contents($this->targetFile('subdir/toc.html'));

        $this->assertContains('../introduction.html#introduction-page', $contents);
        $this->assertContains('../subdirective.html#sub-directives', $contents);
        $this->assertContains('../magic-link.html#another-page', $contents);
        $this->assertContains('test.html#subdirectory', $contents);
    }

    public function testAnchors() : void
    {
        $contents = file_get_contents($this->targetFile('index.html'));

        $this->assertContains('<a id="reference_anchor"></a>', $contents);

        $contents = file_get_contents($this->targetFile('introduction.html'));

        $this->assertContains('<p>Reference to the <a href="index.html#reference_anchor">Summary</a></p>', $contents);
    }

    /**
     * Testing references to other documents
     */
    public function testReferences() : void
    {
        $contents = file_get_contents($this->targetFile('introduction.html'));

        $this->assertContains('<a href="index.html#toc">Index, paragraph toc</a>', $contents);
        $this->assertContains('<a href="index.html">Index</a>', $contents);
        $this->assertContains('<a href="index.html">Summary</a>', $contents);

        $contents = file_get_contents($this->targetFile('subdir/test.html'));

        $this->assertContains('"../index.html"', $contents);
    }

    /**
     * Testing wrapping sub directive
     */
    public function testSubDirective() : void
    {
        $contents = file_get_contents($this->targetFile('subdirective.html'));

        $this->assertEquals(2, substr_count($contents, '<div class="note">'));
        $this->assertEquals(2, substr_count($contents, '<li>'));
        $this->assertContains('</div>', $contents);
        $this->assertEquals(2, substr_count($contents, '</li>'));
        $this->assertEquals(1, substr_count($contents, '<ul>'));
        $this->assertEquals(1, substr_count($contents, '</ul>'));
        $this->assertContains('<p>This is a simple note!</p>', $contents);
        $this->assertContains('<h2>There is a title here</h2>', $contents);
    }

    /**
     * Test that redirection-title worked
     */
    public function testRedirectionTitle() : void
    {
        $contents = file_get_contents($this->targetFile('magic-link.html'));
        $this->assertNotContains('redirection', $contents);

        $contents = file_get_contents($this->targetFile('index.html'));
        $this->assertContains('"subdirective.html">See also', $contents);
    }

    public function setUp() : void
    {
        shell_exec('rm -rf ' . $this->targetFile());
        $builder = new Builder();
        $builder->copy('file.txt');
        $builder->setUseRelativeUrls(true);
        $builder->build($this->sourceFile(), $this->targetFile(), false);
    }

    protected function sourceFile($file = '')
    {
        return __DIR__ . '/builder/input/' . $file;
    }

    protected function targetFile($file = '')
    {
        return __DIR__ . '/builder/output/' . $file;
    }
}
