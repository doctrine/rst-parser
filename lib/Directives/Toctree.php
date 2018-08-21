<?php

declare(strict_types=1);

namespace Doctrine\RST\Directives;

use Doctrine\RST\Directive;
use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;
use function array_merge;
use function count;
use function explode;
use function glob;
use function implode;
use function is_dir;
use function realpath;
use function rtrim;
use function str_repeat;
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
    public function process(Parser $parser, ?Node $node, string $variable, string $data, array $options) : void
    {
        $environment = $parser->getEnvironment();
        $kernel      = $parser->getKernel();
        $files       = [];

        foreach (explode("\n", $node->getValue()) as $file) {
            $file = trim($file);

            if (isset($options['glob']) && strpos($file, '*') !== false) {
                $globPattern = $file;

                $globFiles = $this->globSearch($environment, $globPattern);

                foreach ($globFiles as $globFile) {
                    $dependency = $this->getDependencyFromFile($environment, $globFile);

                    $environment->addDependency($dependency);
                    $files[] = $dependency;
                }
            } elseif ($file !== '') {
                $dependency = $this->getDependencyFromFile($environment, $file);

                $environment->addDependency($dependency);
                $files[] = $dependency;
            }
        }

        $document = $parser->getDocument();
        $document->addNode($kernel->build('Nodes\TocNode', $files, $environment, $options));
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
        $currentFilePath = realpath(rtrim($environment->absoluteRelativePath(''), '/'));
        $rootDocPath     = rtrim(str_replace($environment->getDirName(), '', $currentFilePath), '/');
        $globPatternPath = $rootDocPath . '/' . $globPattern;

        $allFiles = [];

        $files =  glob($globPatternPath);

        foreach ($files as $file) {
            if (is_dir($file)) {
                // remove the root directory so it is a relative path from the root
                $relativePath = str_replace($rootDocPath . '/', '', $file);

                // recursively search in this directory
                $dirFiles = $this->globSearch($environment, $relativePath . '/*');

                $allFiles = array_merge($allFiles, $dirFiles);
            } else {
                // Trim the root path and the .rst extension. This is what the
                // RST parser requires to add a dependency.
                $file = str_replace([$rootDocPath . '/', '.rst'], '', $file);

                $allFiles[] = $file;
            }
        }

        return $allFiles;
    }

    private function getDependencyFromFile(Environment $environment, string $file) : string
    {
        $url = $environment->getUrl();

        $e = explode('/', $url);

        if (count($e) > 1) {
            unset($e[count($e) - 1]);
            $folderPath = implode('/', $e) . '/';

            if (strpos($file, $folderPath) !== false) {
                $file = str_replace($folderPath, '', $file);
            } else {
                $file = str_repeat('../', count($e)) . $file;
            }
        }

        return $file;
    }
}
