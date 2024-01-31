<?php

declare(strict_types=1);

namespace Doctrine\RST\Toc;

use Doctrine\RST\Environment;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;

use function array_merge;
use function realpath;
use function rtrim;
use function str_replace;
use function strrpos;
use function substr;

class GlobSearcher
{
    /**
     * Returns array of absolute URL strings.
     *
     * For example, if you were glob'ing from inside a file called
     * "subdir/file.rst", it would return paths like "/subdir/other-file"
     * and "/subdir/more-dir/file2".
     *
     * @return string[]
     */
    public function globSearch(Environment $environment, string $globPattern): array
    {
        $currentFilePath = Path::normalize((string) realpath(rtrim($environment->absoluteRelativePath(''), '/')));

        $rootDocPath = rtrim(str_replace($environment->getDirName(), '', $currentFilePath), '/');

        if ($globPattern[0] === '/') {
            $globPatternPath = $rootDocPath . $globPattern;
        } else {
            $globPatternPath = $currentFilePath . '/' . $globPattern;
        }

        $pos             = strrpos($globPatternPath, '/');
        $globPatternFile = $pos === false ? $globPatternPath : substr($globPatternPath, $pos + 1);
        $globPatternPath = $pos === false ? '' : substr($globPatternPath, 0, $pos);

        $allFiles = [];

        $finder = new Finder();
        $finder->in($globPatternPath)
            ->name($globPatternFile . '.rst')
            ->files();

        foreach ($finder as $file) {
            if ($file->isDir()) {
                // remove the root directory so it is a relative path from the root
                $relativePath = str_replace($rootDocPath, '', Path::normalize((string) $file->getRealPath()));

                // recursively search in this directory
                $dirFiles = $this->globSearch($environment, $relativePath . '/*');

                $allFiles = array_merge($allFiles, $dirFiles);
            } else {
                // Trim the root path and the .rst extension. This is what the
                // RST parser requires to add a dependency.
                $file = str_replace([$rootDocPath, '.rst'], '', Path::normalize((string) $file->getRealPath()));

                $allFiles[] = $file;
            }
        }

        return $allFiles;
    }
}
