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
use Symfony\Component\Finder\SplFileInfo;
use Throwable;

use function array_map;
use function assert;
use function explode;
use function file_exists;
use function file_get_contents;
use function implode;
use function in_array;
use function is_string;
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

    protected function setUp(): void
    {
        setlocale(LC_ALL, 'en_US.utf8');
    }

    /**
     * @param Format::* $format
     *
     * @dataProvider getBuildTests
     */
    public function testBuild(
        string $file,
        Parser $parser,
        string $format,
        string $expected
    ): void {
        $configuration = new Configuration();
        $configuration->setFileExtension(Format::HTML);
        $configuration->setUseCachedMetas(false);
        $kernel  = new Kernel($configuration);
        $builder = new Builder($kernel);

        $builder->build(__DIR__ . '/tests/build/' . $file, __DIR__ . '/output/build/' . $file);

        $outputFileFinder = new Finder();
        $outputFileFinder
            ->files()
            ->in(__DIR__ . '/output/build/' . $file)
            ->name('index.html');

        foreach ($outputFileFinder as $outputFile) {
            $rendered = $outputFile->getContents();
            self::assertSame(
                $this->trimTrailingWhitespace($expected),
                $this->trimTrailingWhitespace($rendered)
            );
        }
    }

    /**
     * @param 'render'|'renderAll' $renderMethod
     * @param Format::*            $format
     *
     * @dataProvider getRenderTests
     */
    public function testRender(
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

    /** @return iterable<string, array{string, Parser, 'render'|'renderDocument', Format::*, string, string, bool}> */
    public function getRenderTests(): iterable
    {
        $tests = [];

        foreach ($this->findSubDirectories(__DIR__ . '/tests/render') as $dir) {
            $rstFilename = $dir->getPathname() . '/' . $dir->getFilename() . '.rst';
            if (! file_exists($rstFilename)) {
                throw new Exception(sprintf('Could not find functional test file "%s"', $rstFilename));
            }

            $rst = file_get_contents($rstFilename);
            assert(is_string($rst));
            $basename = $dir->getFilename();

            foreach ($this->findRstFiles($dir) as $file) {
                $format = $file->getExtension();
                if (! in_array($format, [Format::HTML, Format::LATEX], true)) {
                    throw new Exception(sprintf('Unexpected file extension in "%s"', $file->getPathname()));
                }

                if (strpos($file->getFilename(), $dir->getFilename()) !== 0) {
                    throw new Exception(sprintf('Test filename "%s" does not match directory name', $file->getPathname()));
                }

                $renderMethod = in_array($basename, self::RENDER_DOCUMENT_FILES, true)
                    ? 'renderDocument'
                    : 'render';

                yield $basename . '_' . $format => [
                    $basename,
                    $this->getParser($format, __DIR__ . '/tests/render/' . $basename),
                    $renderMethod,
                    $format,
                    $rst,
                    trim($file->getContents()),
                    ! in_array($basename, self::SKIP_INDENTER_FILES, true),
                ];
            }
        }
    }

    /** @return iterable<string, array{string, Parser, Format::*, string}> */
    public function getBuildTests(): iterable
    {
        foreach ($this->findSubDirectories(__DIR__ . '/tests/build') as $dir) {
            $rstFilename = $dir->getPathname() . '/index.rst';
            if (! file_exists($rstFilename)) {
                throw new Exception(sprintf('Could not find functional test file "%s"', $rstFilename));
            }

            $basename = $dir->getFilename();

            foreach ($this->findRstFiles($dir) as $file) {
                $format = $file->getExtension();
                if (! in_array($format, [Format::HTML, Format::LATEX], true)) {
                    throw new Exception(sprintf('Unexpected file extension in "%s"', $file->getPathname()));
                }

                if (strpos($file->getFilename(), 'index') !== 0) {
                    throw new Exception(sprintf('Test filename "%s" does not match index', $file->getPathname()));
                }

                yield $basename . '_' . $format => [
                    $basename,
                    $this->getParser($format, __DIR__ . '/tests/build/' . $basename),
                    $format,
                    trim($file->getContents()),
                ];
            }
        }
    }

    /** @return iterable<SplFileInfo> */
    private function findSubDirectories(string $directory): iterable
    {
        $finder = new Finder();

        return $finder
            ->directories()
            ->in($directory);
    }

    /** @return iterable<SplFileInfo> */
    private function findRstFiles(SplFileInfo $dir): iterable
    {
        $fileFinder = new Finder();

        return $fileFinder
            ->files()
            ->in($dir->getPathname())
            ->notName('*.rst')
            ->notName('*.rst.txt');
    }

    /** @param Format::* $format */
    private function getParser(string $format, string $currentDirectory): Parser
    {
        $configuration = new Configuration();
        $configuration->setFileExtension($format);
        $configuration->silentOnError(true);

        $kernel = new Kernel($configuration);
        $parser =  new Parser($kernel);

        $environment = $parser->getEnvironment();
        $environment->setCurrentDirectory($currentDirectory);

        return $parser;
    }

    private function trimTrailingWhitespace(string $string): string
    {
        $lines = explode("\n", $string);

        $lines = array_map(static function (string $line): string {
            return trim($line);
        }, $lines);

        return trim(implode("\n", $lines));
    }
}
