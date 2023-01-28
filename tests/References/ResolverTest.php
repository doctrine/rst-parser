<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\References;

use Doctrine\Common\EventManager;
use Doctrine\RST\Configuration;
use Doctrine\RST\Environment;
use Doctrine\RST\Event\MissingReferenceResolverEvent;
use Doctrine\RST\Meta\MetaEntry;
use Doctrine\RST\Meta\Metas;
use Doctrine\RST\References\ResolvedReference;
use Doctrine\RST\References\Resolver;
use Doctrine\Tests\RST\References\Listener\SimpleMissingReferenceResolverListener;
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

    /** @var Configuration|MockObject */
    private $configuration;

    /** @var Resolver */
    private $resolver;

    protected function setUp(): void
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

        $this->configuration = $this->createMock(Configuration::class);

        $this->resolver = new Resolver();
    }

    public function testResolveFileReference(): void
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
            new ResolvedReference('file', 'title', '/url', [], ['attr' => 'value']),
            $this->resolver->resolve($this->environment, 'url', 'doc', ['attr' => 'value'])
        );
    }

    public function testResolveAnchorReference(): void
    {
        $this->environment->expects(self::once())
            ->method('canonicalUrl')
            ->willReturn(null);

        $this->metas->expects(self::once())
            ->method('findLinkTargetMetaEntry')
            ->willReturn($this->metaEntry);

        $this->environment->expects(self::once())
            ->method('relativeUrl')
            ->willReturn('/url');

        self::assertEquals(
            new ResolvedReference('', 'title', '/url#anchor', [], ['attr' => 'value']),
            $this->resolver->resolve($this->environment, 'doc', 'anchor', ['attr' => 'value'])
        );
    }

    public function testUnResolvedReference1(): void
    {
        $this->environment->expects(self::once())
            ->method('canonicalUrl')
            ->willReturn(null);

        $this->metas->expects(self::once())
            ->method('findLinkTargetMetaEntry')
            ->willReturn(null);

        self::assertNull($this->resolver->resolve($this->environment, 'ref', 'invalid-reference'));
    }

    public function testUnResolvedReference2(): void
    {
        $this->environment->expects(self::once())
            ->method('canonicalUrl')
            ->willReturn('file');

        $this->metas->expects(self::once())
            ->method('get')
            ->willReturn(null);

        $this->metas->expects(self::once())
            ->method('findLinkTargetMetaEntry')
            ->willReturn(null);

        self::assertNull($this->resolver->resolve($this->environment, 'ref', 'invalid-reference'));
    }

    public function testMissingReferenceResolverEventDispatched(): void
    {
        $this->environment
            ->method('getConfiguration')
            ->willReturn($this->configuration);
        $eventManager = new EventManager();
        $eventManager->addEventListener(
            [MissingReferenceResolverEvent::MISSING_REFERENCE_RESOLVER],
            new SimpleMissingReferenceResolverListener()
        );
        $this->configuration
            ->method('getEventManager')
            ->willReturn($eventManager);
        self::assertEquals(
            new ResolvedReference(null, 'example', 'https://example.com/', [], []),
            $this->resolver->resolve($this->environment, 'ref', 'unknown-reference')
        );
    }
}
