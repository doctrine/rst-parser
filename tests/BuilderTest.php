<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Builder;
use Exception;
use PHPUnit\Framework\TestCase;
use function file_exists;
use function file_get_contents;
use function is_dir;
use function shell_exec;
use function substr_count;

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
        self::assertTrue(is_dir($this->targetFile()));
        self::assertTrue(file_exists($this->targetFile('index.html')));
        self::assertTrue(file_exists($this->targetFile('introduction.html')));
        self::assertTrue(file_exists($this->targetFile('subdirective.html')));
        self::assertTrue(file_exists($this->targetFile('magic-link.html')));
        self::assertTrue(file_exists($this->targetFile('file.txt')));
        self::assertTrue(file_exists($this->targetFile('subdir/test.html')));
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

    /**
     * Tests that the index toctree worked
     */
    public function testToctree() : void
    {
        $contents = $this->getFileContents($this->targetFile('index.html'));

        self::assertContains('introduction.html', $contents);
        self::assertContains('Introduction page', $contents);
    }

    public function testToctreeGlob() : void
    {
        $contents = $this->getFileContents($this->targetFile('toc-glob.html'));

        self::assertContains('magic-link.html#another-page', $contents);
        self::assertContains('introduction.html#introduction-page', $contents);
        self::assertContains('subdirective.html', $contents);
        self::assertContains('subdir/test.html#subdirectory', $contents);
    }

    public function testToctreeInSubdirectory() : void
    {
        $contents = $this->getFileContents($this->targetFile('subdir/toc.html'));

        self::assertContains('../introduction.html#introduction-page', $contents);
        self::assertContains('../subdirective.html#sub-directives', $contents);
        self::assertContains('../magic-link.html#another-page', $contents);
        self::assertContains('test.html#subdirectory', $contents);
    }

    public function testAnchors() : void
    {
        $contents = $this->getFileContents($this->targetFile('index.html'));

        self::assertContains('<a id="reference_anchor"></a>', $contents);

        $contents = $this->getFileContents($this->targetFile('introduction.html'));

        self::assertContains('<p>Reference to the <a href="index.html#reference_anchor">Summary</a></p>', $contents);
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

        $contents = $this->getFileContents($this->targetFile('subdir/test.html'));

        self::assertContains('"../index.html"', $contents);
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

    /**
     * Test that redirection-title worked
     */
    public function testRedirectionTitle() : void
    {
        $contents = $this->getFileContents($this->targetFile('magic-link.html'));
        self::assertNotContains('redirection', $contents);

        $contents = $this->getFileContents($this->targetFile('index.html'));
        self::assertContains('"subdirective.html">See also', $contents);
    }

    public function setUp() : void
    {
        shell_exec('rm -rf ' . $this->targetFile());
        $builder = new Builder();
        $builder->copy('file.txt');
        $builder->setUseRelativeUrls(true);
        $builder->build($this->sourceFile(), $this->targetFile(), false);
    }

    private function sourceFile(string $file = '') : string
    {
        return __DIR__ . '/builder/input/' . $file;
    }

    private function targetFile(string $file = '') : string
    {
        return __DIR__ . '/builder/output/' . $file;
    }

    /**
     * @throws Exception
     */
    private function getFileContents(string $path) : string
    {
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new Exception('Could not load file.');
        }

        return $contents;
    }
}
