<?php

declare(strict_types=1);

namespace Doctrine\RST\Toc;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\Node;

use function array_filter;
use function array_map;
use function array_reverse;
use function asort;
use function explode;
use function in_array;
use function strpos;

final class ToctreeBuilder
{
    /** @var GlobSearcher */
    private $globSearcher;

    public function __construct(GlobSearcher $globSearcher)
    {
        $this->globSearcher = $globSearcher;
    }

    /**
     * @param mixed[] $options
     *
     * @return string[]
     */
    public function buildToctreeFiles(
        Environment $environment,
        Node $node,
        array $options
    ): array {
        $toctreeFiles = [];

        foreach ($this->parseToctreeFiles($node) as $file) {
            if ($this->isGlob($options, $file)) {
                $globPattern = $file;

                $globFiles = $this->globSearcher
                    ->globSearch($environment, $globPattern);

                asort($globFiles);

                foreach ($globFiles as $globFile) {
                    // if glob finds a file already explicitly defined
                    // don't duplicate it in the toctree again
                    if (in_array($globFile, $toctreeFiles, true)) {
                        continue;
                    }

                    if ($globFile === $environment->absoluteUrl($environment->getCurrentFileName())) {
                        // filter out the current file from being added as a glob
                        continue;
                    }

                    $toctreeFiles[] = $globFile;
                }
            } else {
                $absoluteUrl = $environment->absoluteUrl($file);

                $toctreeFiles[] = $absoluteUrl;
            }
        }

        if (isset($options['reversed'])) {
            $toctreeFiles = array_reverse($toctreeFiles);
        }

        return $toctreeFiles;
    }

    /** @return string[] */
    private function parseToctreeFiles(Node $node): array
    {
        return array_filter(array_map('trim', explode(
            "\n",
            $node->getValueString()
        )), static function (string $file): bool {
            return $file !== '';
        });
    }

    /** @param mixed[] $options */
    private function isGlob(array $options, string $file): bool
    {
        return isset($options['glob']) && strpos($file, '*') !== false;
    }
}
