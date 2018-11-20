<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\LaTeX;

use Doctrine\RST\Configuration;
use Doctrine\RST\Formats\Format;
use Doctrine\RST\Kernel;
use Doctrine\RST\Parser;
use PHPUnit\Framework\TestCase;
use function trim;

class LaTeXTest extends TestCase
{
    public function testAnchor() : void
    {
        $rendered = $this->parse('anchor.rst');

        $expected = <<<RST
\\label{}
\\label{anchor}
\\ref{#anchor}
RST;

        self::assertContains($expected, $rendered);
    }

    public function testCode() : void
    {
        $rendered = $this->parse('code.rst');

        $expected = <<<RST
\\label{}
\\lstset{language=php}
\\begin{lstlisting}
This is a code block

You hou!

\\end{lstlisting}
RST;

        self::assertContains($expected, $rendered);
    }

    public function testImage() : void
    {
        $rendered = $this->parse('image.rst');

        $expected = <<<RST
\\label{}
\\includegraphics{test.jpg}
\\includegraphics{try.jpg}
\\includegraphics{other.jpg}
RST;

        self::assertContains($expected, $rendered);
    }

    public function testList() : void
    {
        $rendered = $this->parse('list.rst');

        $expected = <<<RST
\\label{}
\\begin{itemize}\item This is
\\item A simple
\\item Unordered
  With an other line
\\item Last line
\\end{itemize}
RST;

        self::assertContains($expected, $rendered);
    }

    public function testParagraph() : void
    {
        $rendered = $this->parse('paragraph.rst');

        $expected = <<<RST
\\label{}
Test paragraph!
RST;

        self::assertContains($expected, $rendered);
    }

    public function testQuote() : void
    {
        $rendered = $this->parse('quote.rst');

        $expected = <<<RST
\\label{}
This is a quote:
\\begin{quotation}
Quoting someone
On some lines

\\end{quotation}
RST;

        self::assertContains($expected, $rendered);
    }

    public function testSeparator() : void
    {
        $rendered = $this->parse('separator.rst');

        $expected = <<<RST
\\label{}
Testing separator
\ \
Hey!
RST;

        self::assertContains($expected, $rendered);
    }

    public function testTable() : void
    {
        $rendered = $this->parse('table.rst');

        $expected = <<<RST
\\label{}
\\begin{tabular}{|l|l|l|}
\\hline
Col A & Col B & Col C \\\\
\\hline
Col X & Col Y & Col Z \\\\
\\hline
Col U & Col J & Col K \\\\
\\hline

\\end{tabular}
RST;

        self::assertContains($expected, $rendered);
    }

    public function testTitle() : void
    {
        $rendered = $this->parse('title.rst');

        $expected = <<<RST
\\label{}

\\chapter{Test title}
RST;

        self::assertContains($expected, $rendered);
    }

    public function testToc() : void
    {
        $rendered = $this->parse('toc.rst');

        $expected = <<<RST
\\label{}

\\chapter{Title 1}


\\chapter{Title 2}
\\tableofcontents
RST;

        self::assertContains($expected, $rendered);
    }

    public function testMain() : void
    {
        $rendered = $this->parse('main.rst');

        $expected = <<<RST
\\documentclass[11pt]{report}
\\usepackage[utf8]{inputenc}
\\usepackage[T1]{fontenc}
\\usepackage[french]{babel}
\\usepackage{cite}
\\usepackage{amssymb}
\\usepackage{amsmath}
\\usepackage{mathrsfs}
\\usepackage{graphicx}
\\usepackage{hyperref}
\\usepackage{listings}
\\begin{document}
\\label{}
Test


\\end{document}
RST;

        self::assertContains($expected, $rendered);
    }

    private function parse(string $file) : string
    {
        $directory = __DIR__ . '/files/';

        $configuration = new Configuration();
        $configuration->setIgnoreInvalidReferences(true);
        $configuration->setFileExtension(Format::LATEX);

        $kernel      = new Kernel($configuration);
        $parser      = new Parser($kernel);
        $environment = $parser->getEnvironment();
        $environment->setCurrentDirectory($directory);

        return trim($parser->parseFile($directory . $file)->renderDocument());
    }
}
