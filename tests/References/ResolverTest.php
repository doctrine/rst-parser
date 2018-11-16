<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\References;

use Doctrine\RST\Environment;
use Doctrine\RST\Meta\MetaEntry;
use Doctrine\RST\Meta\Metas;
use Doctrine\RST\References\ResolvedReference;
use Doctrine\RST\References\Resolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ResolverTest extends TestCase
{
    /** @var Environment|MockObject */
    private $environment;

    /** @var Metas|MockObject */
    private $metas;

    /** @var MetaEntry|MockObject */
    private $metaEntry;

    /** @var Resolver */
    private $resolver;

    protected function setUp() : void
    {
        $this->environment = $this->createMock(Environment::class);

        $this->metas = $this->createMock(Metas::class);
        $this->environment->expects(self::any())
            ->method('getMetas')
            ->willReturn($this->metas);

        $this->metaEntry = $this->createMock(MetaEntry::class);
        $this->metaEntry->expects(self::any())
            ->method('getUrl')
            ->willReturn('url');

        $this->metaEntry->expects(self::any())
            ->method('getTitle')
            ->willReturn('title');

        $this->metaEntry->expects(self::any())
            ->method('getTitles')
            ->willReturn([]);

        $this->resolver = new Resolver();
    }

    public function testResolveFileReference() : void
    {
        $this->environment->expects(self::once())
            ->method('canonicalUrl')
            ->willReturn('file');

        $this->metas->expects(self::once())
            ->method('get')
            ->willReturn($this->metaEntry);

        $this->environment->expects(self::once())
            ->method('relativeUrl')
            ->willReturn('/url');

        self::assertEquals(
            new ResolvedReference('title', '/url', [], ['attr' => 'value']),
            $this->resolver->resolve($this->environment, 'url', ['attr' => 'value'])
        );
    }

    public function testResolveAnchorReference() : void
    {
        $this->environment->expects(self::once())
            ->method('canonicalUrl')
            ->willReturn(null);

        $this->metas->expects(self::once())
            ->method('findLinkMetaEntry')
            ->willReturn($this->metaEntry);

        $this->environment->expects(self::once())
            ->method('relativeUrl')
            ->willReturn('/url');

        self::assertEquals(
            new ResolvedReference('title', '/url#anchor', [], ['attr' => 'value']),
            $this->resolver->resolve($this->environment, 'anchor', ['attr' => 'value'])
        );
    }

    public function testUnResolvedReference1() : void
    {
        $this->environment->expects(self::once())
            ->method('canonicalUrl')
            ->willReturn(null);

        $this->metas->expects(self::once())
            ->method('findLinkMetaEntry')
            ->willReturn(null);

        self::assertNull($this->resolver->resolve($this->environment, 'invalid-reference'));
    }

    public function testUnResolvedReference2() : void
    {
        $this->environment->expects(self::once())
            ->method('canonicalUrl')
            ->willReturn('file');

        $this->metas->expects(self::once())
            ->method('get')
            ->willReturn(null);

        $this->metas->expects(self::once())
            ->method('findLinkMetaEntry')
            ->willReturn(null);

        self::assertNull($this->resolver->resolve($this->environment, 'invalid-reference'));
    }
}
