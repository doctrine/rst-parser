<?php

declare(strict_types=1);

namespace Doctrine\RST\Meta;

use LogicException;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function sprintf;

final class CachedMetasLoader
{
    public function loadCachedMetaEntries(string $targetDirectory, Metas $metas): void
    {
        $metaCachePath = $this->getMetaCachePath($targetDirectory);
        if (! file_exists($metaCachePath)) {
            return;
        }

        $contents = file_get_contents($metaCachePath);

        if ($contents === false) {
            throw new LogicException(sprintf('Could not load file "%s"', $contents));
        }

        $metas->unserialize($contents);
    }

    public function cacheMetaEntries(string $targetDirectory, Metas $metas): void
    {
        file_put_contents($this->getMetaCachePath($targetDirectory), $metas->serialize());
    }

    private function getMetaCachePath(string $targetDirectory): string
    {
        return $targetDirectory . '/metas.php';
    }
}
