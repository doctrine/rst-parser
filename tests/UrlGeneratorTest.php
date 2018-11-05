<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Configuration;
use Doctrine\RST\UrlGenerator;
use PHPUnit\Framework\TestCase;

class UrlGeneratorTest extends TestCase
{
    public function testGenerateUrlRelative() : void
    {
        $configuration = $this->createMock(Configuration::class);

        $urlGenerator = new UrlGenerator($configuration);

        self::assertSame('../index', $urlGenerator->generateUrl('/index', 'subdir/index', ''));
        self::assertSame('index', $urlGenerator->generateUrl('/subdir/index', 'subdir/index', ''));
        self::assertSame('subdir/index', $urlGenerator->generateUrl('/subdir/index', 'index', ''));
    }

    public function testGenerateUrlAbsoluteBaseUrl() : void
    {
        $configuration = $this->createMock(Configuration::class);

        $configuration->expects(self::once())
            ->method('isBaseUrlEnabled')
            ->with('path')
            ->willReturn(true);

        $configuration->expects(self::once())
            ->method('getBaseUrl')
            ->willReturn('https://www.domain.com/directory/');

        $urlGenerator = new UrlGenerator($configuration);

        self::assertSame(
            'https://www.domain.com/directory/path',
            $urlGenerator->generateUrl('/path', 'path', '')
        );
    }

    public function testAbsoluteUrl() : void
    {
        $configuration = $this->createMock(Configuration::class);

        $urlGenerator = new UrlGenerator($configuration);

        self::assertSame('/test', $urlGenerator->absoluteUrl('/', '/test'));

        self::assertSame('/test', $urlGenerator->absoluteUrl('/subdir', '/test'));

        self::assertSame('/test', $urlGenerator->absoluteUrl('/', 'test'));

        self::assertSame('/subdir/test', $urlGenerator->absoluteUrl('/subdir', 'test'));
    }

    public function testRelativeUrl() : void
    {
        $configuration = $this->createMock(Configuration::class);

        $urlGenerator = new UrlGenerator($configuration);

        self::assertNull($urlGenerator->relativeUrl(null, ''));

        self::assertSame('://test', $urlGenerator->relativeUrl('://test', ''));

        self::assertSame('test', $urlGenerator->relativeUrl('test', '/'));

        self::assertSame('../../test', $urlGenerator->relativeUrl('/test', '/subdir1/subdir2'));

        self::assertSame('../../subdir1/subdir2/test', $urlGenerator->relativeUrl('/subdir1/subdir2/test', '/subdir1/subdir2'));
    }

    public function testCanonicalUrl() : void
    {
        $configuration = $this->createMock(Configuration::class);

        $urlGenerator = new UrlGenerator($configuration);

        self::assertSame('dir/file', $urlGenerator->canonicalUrl('dir', 'file'));

        self::assertSame('file', $urlGenerator->canonicalUrl('dir', '../file'));

        self::assertSame('dir/file', $urlGenerator->canonicalUrl('dir/subdir', '../file'));

        self::assertSame('file', $urlGenerator->canonicalUrl('dir/subdir', '../../file'));
    }
}
