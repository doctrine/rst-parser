<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\References\Listener;

use Doctrine\RST\Event\MissingReferenceResolverEvent;
use Doctrine\RST\References\ResolvedReference;

class SimpleMissingReferenceResolverListener
{
    public function resolveMissingReference(MissingReferenceResolverEvent $event): void
    {
        $file              = null;
        $title             = 'example';
        $url               = 'https://example.com/';
        $resolvedReference = new ResolvedReference($file, $title, $url);
        $event->setResolvedReference($resolvedReference);
    }
}
