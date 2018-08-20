<?php

namespace Gregwar\RST\Directives;

use Gregwar\RST\Directive;
use Gregwar\RST\Environment;
use Gregwar\RST\Parser;

class Toctree extends Directive
{
    public function getName()
    {
        return 'toctree';
    }

    public function process(Parser $parser, $node, $variable, $data, array $options)
    {
        $environment = $parser->getEnvironment();
        $kernel = $parser->getKernel();
        $files = array();

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

            } elseif ($file) {
                $dependency = $this->getDependencyFromFile($environment, $file);

                $environment->addDependency($dependency);
                $files[] = $dependency;
            }
        }

        $document = $parser->getDocument();
        $document->addNode($kernel->build('Nodes\TocNode', $files, $environment, $options));
    }

    public function wantCode()
    {
        return true;
    }

    private function globSearch(Environment $environment, string $globPattern)
    {
        $currentFilePath = realpath(rtrim($environment->absoluteRelativePath(''), '/'));
        $rootDocPath = rtrim(str_replace($environment->getDirName(), '', $currentFilePath), '/');
        $globPatternPath = $rootDocPath.'/'.$globPattern;

        $allFiles = [];

        $files =  glob($globPatternPath);

        foreach ($files as $file) {
            if (is_dir($file)) {
                // remove the root directory so it is a relative path from the root
                $relativePath = str_replace($rootDocPath.'/', '', $file);

                // recursively search in this directory
                $dirFiles = $this->globSearch($environment, $relativePath.'/*');

                $allFiles = array_merge($allFiles, $dirFiles);
            } else {
                // Trim the root path and the .rst extension. This is what the
                // RST parser requires to add a dependency.
                $file = str_replace([$rootDocPath.'/', '.rst'], '', $file);

                $allFiles[] = $file;
            }
        }

        return $allFiles;
    }

    private function getDependencyFromFile(Environment $environment, string $file)
    {
        $url = $environment->getUrl();

        $e = explode('/', $url);

        if (count($e) > 1) {
            unset($e[count($e) - 1]);
            $folderPath = implode('/', $e).'/';

            if (strpos($file, $folderPath) !== false) {
                $file = str_replace($folderPath, '', $file);
            } else {
                $file = str_repeat('../', count($e)).$file;
            }
        }

        return $file;
    }
}
