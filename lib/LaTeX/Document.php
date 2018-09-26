<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX;

use Doctrine\RST\Document as Base;
use Doctrine\RST\LaTeX\Nodes\LaTeXMainNode;
use function count;

class Document extends Base
{
    public function render() : string
    {
        $document = '';
        foreach ($this->nodes as $node) {
            $document .= $node->render() . "\n";
        }

        return $document;
    }

    public function renderDocument() : string
    {
        $isMain = count($this->getNodes(static function ($node) {
            return $node instanceof LaTeXMainNode;
        })) !== 0;

        $document = '';

        if ($isMain) {
            $document .= "\\documentclass[11pt]{report}\n";
            $document .= "\\usepackage[utf8]{inputenc}\n";
            $document .= "\\usepackage[T1]{fontenc}\n";
            $document .= "\\usepackage[french]{babel}\n";
            $document .= "\\usepackage{cite}\n";
            $document .= "\\usepackage{amssymb}\n";
            $document .= "\\usepackage{amsmath}\n";
            $document .= "\\usepackage{mathrsfs}\n";
            $document .= "\\usepackage{graphicx}\n";
            $document .= "\\usepackage{hyperref}\n";
            $document .= "\\usepackage{listings}\n";

            foreach ($this->headerNodes as $node) {
                $document .= $node->render() . "\n";
            }
            $document .= "\\begin{document}\n";
        }

        $document .= '\label{' . $this->environment->getUrl() . "}\n";
        $document .= $this->render();

        if ($isMain) {
            $document .= "\\end{document}\n";
        }

        return $document;
    }
}
