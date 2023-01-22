<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\Functional;

use Doctrine\RST\Builder;
use Doctrine\RST\Configuration;
use Doctrine\RST\ErrorManager;
use Doctrine\RST\ErrorManager\ErrorManagerFactory;
use Doctrine\RST\Formats\Format;
use Doctrine\RST\Parser;
use DOMDocument;
use Exception;
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
use function preg_replace;
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

    public function testBuildDocs(): void
    {
        $configuration = new Configuration();
        $configuration->setFileExtension(Format::HTML);
        $configuration->setUseCachedMetas(false);

        $errorManager        = $this->createMock(ErrorManager::class);
        $errorManagerFactory = $this->createMock(ErrorManagerFactory::class);
        $configuration->setErrorManagerFactory($errorManagerFactory);
        $errorManagerFactory->method('getErrorManager')->willReturn($errorManager);
        $errorManager->expects(self::never())->method('warning');
        $errorManager->expects(self::never())->method('error');
        $builder = new Builder($configuration);
        $builder->build(__DIR__ . '/../../docs/en/', __DIR__ . '/output/docs/');
        self::assertFileExists(__DIR__ . '/output/docs/index.html');
        self::assertFileExists(__DIR__ . '/output/docs/metas.php');
        self::assertFileExists(__DIR__ . '/output/docs/attribution.html');
        $contents = $this->getFileContents(__DIR__ . '/output/docs/attribution.html');
        self::assertStringContainsString('This repository was forked from <a href="https://github.com/Gregwar/RST">Gregwar</a>', $contents);
    }

    /** @throws Exception */
    protected function getFileContents(string $path): string
    {
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new Exception('Could not load file.');
        }

        return $contents;
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
        $configuration->setFileExtension($format);
        $configuration->setUseCachedMetas(false);
        $builder = new Builder($configuration);

        $builder->build(__DIR__ . '/tests/build/' . $file, __DIR__ . '/output/build/' . $file);

        $outputFileFinder = new Finder();
        $outputFileFinder
            ->files()
            ->in(__DIR__ . '/output/build/' . $file)
            ->name('index.' . $format);

        foreach ($outputFileFinder as $outputFile) {
            $rendered = $outputFile->getContents();
            if ($format === Format::HTML) {
                $this->compareHtml($expected, $rendered);
            } else {
                self::assertEquals(trim($expected), trim($rendered));
            }
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

        if ($format === Format::HTML) {
            $this->compareHtml($expected, $rendered);
        } else {
            self::assertEquals(trim($expected), trim($rendered));
        }
    }

    private function compareHtml(string $expected, string $rendered): void
    {
        $rendered = $this->removeRedundantWhitespaceFromHtml($rendered);
        $expected = $this->removeRedundantWhitespaceFromHtml($expected);
        try {
            // try to compare as HTML
            $expectedDom = new DomDocument();
            $expectedDom->loadHTML($expected);
            $expectedDom->preserveWhiteSpace = false;

            $actualDom = new DomDocument();
            $actualDom->loadHTML($rendered);
            $actualDom->preserveWhiteSpace = false;

            $expectedHtml = $expectedDom->saveHTML();
            $actualHtml   = $actualDom->saveHTML();

            self::assertIsString($expectedHtml);
            self::assertIsString($actualHtml);

            self::assertXmlStringEqualsXmlString($expectedHtml, $actualHtml);
        } catch (Throwable $e) {
            // if this fails compare as string
            self::assertEquals(trim($expected), trim($rendered));
        }
    }

    private function removeRedundantWhitespaceFromHtml(string $html): string
    {
        $html = implode("\n", array_map('trim', explode("\n", $html)));
        $html = preg_replace('#\n+#', "\n", $html);
        $html = preg_replace('#\s+#', ' ', $html);
        $html = preg_replace('#\s<#', '<', $html);
        $html = preg_replace('#>\s#', '>', $html);
        $html = preg_replace('#\s/>#', '/>', $html);

        return $html;
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
            ->in($directory)
            ->notName('_*');
    }

    /** @return iterable<SplFileInfo> */
    private function findRstFiles(SplFileInfo $dir): iterable
    {
        $fileFinder = new Finder();

        return $fileFinder
            ->files()
            ->in($dir->getPathname())
            ->notName('*.rst')
            ->notName('*.rst.txt')
            ->notName('_*.*');
    }

    /** @param Format::* $format */
    private function getParser(string $format, string $currentDirectory): Parser
    {
        $configuration = new Configuration();
        $configuration->setFileExtension($format);
        $configuration->silentOnError(true);

        $parser =  new Parser($configuration);

        $environment = $parser->getEnvironment();
        $environment->setCurrentDirectory($currentDirectory);

        return $parser;
    }
}
