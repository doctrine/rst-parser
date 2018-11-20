<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\Functional;

use Doctrine\RST\Configuration;
use Doctrine\RST\Formats\Format;
use Doctrine\RST\Kernel;
use Doctrine\RST\Parser;
use Gajus\Dindent\Indenter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use function array_map;
use function basename;
use function explode;
use function file_exists;
use function file_get_contents;
use function implode;
use function in_array;
use function rtrim;
use function str_replace;
use function strpos;
use function trim;

class FunctionalTest extends TestCase
{
    private const RENDER_DOCUMENT_FILES = ['main-directive'];

    /**
     * @dataProvider getFunctionalTests
     */
    public function testFunctional(
        string $file,
        Parser $parser,
        string $renderMethod,
        string $format,
        string $rst,
        string $expected
    ) : void {
        $expectedLines = explode("\n", $expected);
        $firstLine     = $expectedLines[0];

        if (strpos($firstLine, 'Exception:') === 0) {
            $exceptionClass = str_replace('Exception: ', '', $firstLine);
            $this->expectException($exceptionClass);

            $expectedExceptionMessage = $expectedLines;
            unset($expectedExceptionMessage[0]);
            $expectedExceptionMessage = implode("\n", $expectedExceptionMessage);

            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        $document = $parser->parse($rst);

        $rendered = $document->$renderMethod();

        if ($format === Format::HTML) {
            $indenter = new Indenter();
            $rendered = $indenter->indent($rendered);
        }

        self::assertSame(
            $this->trimTrailingWhitespace($expected),
            $this->trimTrailingWhitespace($rendered)
        );
    }

    /**
     * @return mixed[]
     */
    public function getFunctionalTests() : array
    {
        $finder = new Finder();
        $finder
            ->files()
            ->in(__DIR__ . '/tests')
            ->name('*.rst');

        $tests = [];

        foreach ($finder as $file) {
            $rst      = $file->getContents();
            $filename = $file->getFilename();
            $basename = basename($filename, '.rst');

            $dir = $file->getPathInfo();

            $formats = [Format::HTML, Format::LATEX];

            foreach ($formats as $format) {
                $formatPath = $dir . '/' . $basename . '.' . $format;

                if (! file_exists($formatPath)) {
                    continue;
                }

                $expected = file_get_contents($formatPath);

                if ($expected === false) {
                    continue;
                }

                $configuration = new Configuration();
                $configuration->setFileExtension($format);

                $kernel = new Kernel($configuration);
                $parser = new Parser($kernel);

                $environment = $parser->getEnvironment();
                $environment->setCurrentDirectory(__DIR__ . '/tests/' . $basename);

                $renderMethod = in_array($basename, self::RENDER_DOCUMENT_FILES, true)
                    ? 'renderDocument'
                    : 'render';

                $tests[] = [$basename, $parser, $renderMethod, $format, $rst, trim($expected)];
            }
        }

        return $tests;
    }

    private function trimTrailingWhitespace(string $string) : string
    {
        $lines = explode("\n", $string);

        $lines = array_map(static function (string $line) {
            return rtrim($line);
        }, $lines);

        return trim(implode("\n", $lines));
    }
}
