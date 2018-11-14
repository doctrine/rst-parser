<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Renderers;

use Doctrine\RST\Nodes\DocumentNode;
use Doctrine\RST\Nodes\MainNode;
use Doctrine\RST\Renderers\DocumentNodeRenderer as BaseDocumentRender;
use Doctrine\RST\Renderers\FullDocumentNodeRenderer;
use Doctrine\RST\Renderers\NodeRenderer;
use function count;

class DocumentNodeRenderer implements NodeRenderer, FullDocumentNodeRenderer
{
    /** @var DocumentNode */
    private $document;

    public function __construct(DocumentNode $document)
    {
        $this->document = $document;
    }

    public function render() : string
    {
        return (new BaseDocumentRender($this->document))->render();
    }

    public function renderDocument() : string
    {
        $isMain = count($this->document->getNodes(static function ($node) {
            return $node instanceof MainNode;
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

            foreach ($this->document->getHeaderNodes() as $node) {
                $document .= $node->render() . "\n";
            }
            $document .= "\\begin{document}\n";
        }

        $document .= '\label{' . $this->document->getEnvironment()->getUrl() . "}\n";
        $document .= $this->render();

        if ($isMain) {
            $document .= "\\end{document}\n";
        }

        return $document;
    }
}
