<?php

declare(strict_types=1);

namespace Doctrine\RST\Directives;

use Doctrine\RST\Directive;
use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;
use Symfony\Component\Finder\Finder;
use function array_merge;
use function explode;
use function implode;
use function glob;
use function is_dir;
use function realpath;
use function rtrim;
use function str_replace;
use function strpos;
use function trim;

class Toctree extends Directive
{
    public function getName() : string
    {
        return 'toctree';
    }

    /**
     * @param string[] $options
     */
    public function process(
        Parser $parser,
        ?Node $node,
        string $variable,
        string $data,
        array $options
    ) : void {
        if ($node === null) {
            return;
        }

        $environment = $parser->getEnvironment();

        $kernel = $parser->getKernel();

        $files = [];

        $value = (string) $node->getValue();

        foreach (explode("\n", $value) as $file) {
            $file = trim($file);

            if (isset($options['glob']) && strpos($file, '*') !== false) {
                $globPattern = $file;

                $globFiles = $this->globSearch($environment, $globPattern);

                foreach ($globFiles as $globFile) {
                    $environment->addDependency($globFile);
                    $files[] = $globFile;
                }
            } elseif ($file !== '') {
                $dependency = $environment->absoluteUrl($file);

                $environment->addDependency($dependency);
                $files[] = $dependency;
            }
        }

        $tocNode = $kernel->getNodeFactory()->createToc($environment, $files, $options);

        $parser->getDocument()->addNode($tocNode);
    }

    public function wantCode() : bool
    {
        return true;
    }

    /**
     * @return string[]
     */
    private function globSearch(Environment $environment, string $globPattern) : array
    {
        $currentFilePath = (string) realpath(rtrim($environment->absoluteRelativePath(''), '/'));

        $rootDocPath = rtrim(str_replace($environment->getDirName(), '', $currentFilePath), '/');

        $globPatternPath = $currentFilePath . '/' . $globPattern;

        $allFiles = [];

        $finder = new Finder();
        $finder->in(rtrim($globPatternPath, '*'))
            ->name('*.rst')
            ->files();

        foreach ($finder as $file) {
            if ($file->isDir()) {
                // remove the root directory so it is a relative path from the root
                $relativePath = str_replace($rootDocPath . '/', '', (string) $file->getRealPath());

                // recursively search in this directory
                $dirFiles = $this->globSearch($environment, $relativePath . '/*');

                $allFiles = array_merge($allFiles, $dirFiles);
            } else {
                // Trim the root path and the .rst extension. This is what the
                // RST parser requires to add a dependency.
                $file = str_replace([$rootDocPath . '/', '.rst'], '', (string) $file->getRealPath());

                $allFiles[] = $file;
            }
        }

        return $allFiles;
    }
}
