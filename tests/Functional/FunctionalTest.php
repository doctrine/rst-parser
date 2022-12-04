<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\Functional;

use Doctrine\RST\Builder;
use Doctrine\RST\Configuration;
use Doctrine\RST\Formats\Format;
use Doctrine\RST\Kernel;
use Doctrine\RST\Parser;
use Exception;
use Gajus\Dindent\Indenter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Throwable;

use function array_map;
use function assert;
use function explode;
use function file_exists;
use function file_get_contents;
use function implode;
use function in_array;
use function is_string;
use function rtrim;
use function setlocale;
use function sprintf;
use function str_replace;
use function strpos;
use function trim;

use const LC_ALL;

class FunctionalTest extends TestCase
{
    private const RENDER_DOCUMENT_FILES = ['main-directive'];
    private const SKIP_INDENTER_FILES   = ['code-block-diff'];
    private const RENDER_ALL            = ['toctree'];

    protected function setUp(): void
    {
        setlocale(LC_ALL, 'en_US.utf8');
    }

    /** @dataProvider getFunctionalTests */
    public function testFunctional(
        string $file,
        Parser $parser,
        string $renderMethod,
        string $format,
        string $rst,
        string $expected,
        bool $useIndenter = true
    ): void {
        $expectedLines = explode("\n", $expected);
        $firstLine     = $expectedLines[0];

        if (strpos($firstLine, 'Exception:') === 0) {
            /** @psalm-var class-string<Throwable> */
            $exceptionClass = str_replace('Exception: ', '', $firstLine);
            $this->expectException($exceptionClass);

            $expectedExceptionMessage = $expectedLines;
            unset($expectedExceptionMessage[0]);
            $expectedExceptionMessage = implode("\n", $expectedExceptionMessage);

            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        if ($renderMethod === 'renderAll') {
            $configuration = new Configuration();
            $configuration->setFileExtension(Format::HTML);
            $builder = new Builder();

            $builder->build(__DIR__ . '/tests/' . $file, __DIR__ . '/output/' . $file);

            $outputFileFinder = new Finder();
            $outputFileFinder
                ->files()
                ->in(__DIR__ . '/output/' . $file)
                ->name('index.html');

            foreach ($outputFileFinder as $outputFile) {
                $rendered = $outputFile->getContents();
                self::assertSame(
                    $this->trimTrailingWhitespace($expected),
                    $this->trimTrailingWhitespace($rendered)
                );
            }
        } else {
            $document = $parser->parse($rst);

            $rendered = $document->$renderMethod();

            if ($format === Format::HTML && $useIndenter) {
                $indenter = new Indenter();
                $rendered = $indenter->indent($rendered);
            }

            self::assertSame(
                $this->trimTrailingWhitespace($expected),
                $this->trimTrailingWhitespace($rendered)
            );
        }
    }

    /** @return array<string, array{string, Parser, string, string, string, string, bool}> */
    public function getFunctionalTests(): array
    {
        $finder = new Finder();
        $finder
            ->directories()
            ->in(__DIR__ . '/tests');

        $tests = [];

        foreach ($finder as $dir) {
            $rstFilename   = $dir->getPathname() . '/' . $dir->getFilename() . '.rst';
            $indexFilename = $dir->getPathname() . '/index.rst';
            if (file_exists($rstFilename)) {
                $rst = file_get_contents($rstFilename);
            } elseif (file_exists($indexFilename)) {
                $rst = file_get_contents($indexFilename);
            } else {
                throw new Exception(sprintf('Could not find functional test file "%s" or "%s"', $rstFilename, $indexFilename));
            }
            assert(is_string($rst));
            $basename = $dir->getFilename();

            $formats = [Format::HTML, Format::LATEX];

            $fileFinder = new Finder();
            $fileFinder
                ->files()
                ->in($dir->getPathname())
                ->notName('*.rst');
            foreach ($fileFinder as $file) {
                $format = $file->getExtension();
                if (! in_array($format, $formats, true)) {
                    throw new Exception(sprintf('Unexpected file extension in "%s"', $file->getPathname()));
                }

                if (
                    strpos($file->getFilename(), $dir->getFilename()) !== 0
                    && strpos($file->getFilename(), 'index') !== 0
                ) {
                    throw new Exception(sprintf('Test filename "%s" does not match directory name or index', $file->getPathname()));
                }

                $expected = $file->getContents();

                $configuration = new Configuration();
                $configuration->setFileExtension($format);
                $configuration->silentOnError(true);

                $kernel = new Kernel($configuration);
                $parser = new Parser($kernel);

                $environment = $parser->getEnvironment();
                $environment->setCurrentDirectory(__DIR__ . '/tests/' . $basename);

                $renderMethod = in_array($basename, self::RENDER_DOCUMENT_FILES, true)
                    ? 'renderDocument'
                    : 'render';

                $renderMethod = in_array($basename, self::RENDER_ALL, true)
                    ? 'renderAll'
                    : $renderMethod;

                $useIndenter = ! in_array($basename, self::SKIP_INDENTER_FILES, true);

                if (
                    ($renderMethod === 'renderAll' && ! file_exists($indexFilename))
                    || ($renderMethod !== 'renderAll' && ! file_exists($rstFilename))
                ) {
                    throw new Exception(sprintf('The rendering method "%s" expects file "%s" to exist', $renderMethod, $renderMethod === 'renderAll' ? $indexFilename : $rstFilename));
                }

                $tests[$basename . '_' . $format] = [$basename, $parser, $renderMethod, $format, $rst, trim($expected), $useIndenter];
            }
        }

        return $tests;
    }

    private function trimTrailingWhitespace(string $string): string
    {
        $lines = explode("\n", $string);

        $lines = array_map(static function (string $line): string {
            return rtrim($line);
        }, $lines);

        return trim(implode("\n", $lines));
    }
}
