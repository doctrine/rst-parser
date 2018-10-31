<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\UrlGenerator;
use PHPUnit\Framework\TestCase;

class UrlGeneratorTest extends TestCase
{
    /** @var UrlGenerator */
    private $urlGenerator;

    public function testAbsoluteUrl() : void
    {
        self::assertSame('/test', $this->urlGenerator->absoluteUrl('/', '/test'));

        self::assertSame('/test', $this->urlGenerator->absoluteUrl('/subdir', '/test'));

        self::assertSame('/test', $this->urlGenerator->absoluteUrl('/', 'test'));

        self::assertSame('/subdir/test', $this->urlGenerator->absoluteUrl('/subdir', 'test'));
    }

    public function testRelativeUrl() : void
    {
        self::assertNull($this->urlGenerator->relativeUrl(null, ''));

        self::assertSame('://test', $this->urlGenerator->relativeUrl('://test', ''));

        self::assertSame('test', $this->urlGenerator->relativeUrl('test', '/'));

        self::assertSame('../../test', $this->urlGenerator->relativeUrl('/test', '/subdir1/subdir2'));

        self::assertSame('../../subdir1/subdir2/test', $this->urlGenerator->relativeUrl('/subdir1/subdir2/test', '/subdir1/subdir2'));
    }

    public function testCanonicalUrl() : void
    {
        self::assertSame('dir/file', $this->urlGenerator->canonicalUrl('dir', 'file'));

        self::assertSame('file', $this->urlGenerator->canonicalUrl('dir', '../file'));

        self::assertSame('dir/file', $this->urlGenerator->canonicalUrl('dir/subdir', '../file'));

        self::assertSame('file', $this->urlGenerator->canonicalUrl('dir/subdir', '../../file'));
    }

    protected function setUp() : void
    {
        $this->urlGenerator = new UrlGenerator();
    }
}
