<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\TocNode;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;
use Doctrine\RST\Utility\TitleLinkUtility;

final class TocNodeRenderer implements NodeRenderer
{
    /** @var Environment */
    private $environment;

    /** @var TocNode */
    private $tocNode;

    /** @var TemplateRenderer */
    private $templateRenderer;

    public function __construct(Environment $environment, TocNode $tocNode, TemplateRenderer $templateRenderer)
    {
        $this->environment      = $environment;
        $this->tocNode          = $tocNode;
        $this->templateRenderer = $templateRenderer;
    }

    public function render(): string
    {
        $options = $this->tocNode->getOptions();

        if (isset($options['hidden'])) {
            return '';
        }

        $tocItems = [];

        $titleLinkUtility = new TitleLinkUtility($this->environment, $this->tocNode->isTitlesOnly()?1:$this->tocNode->getDepth());

        foreach ($this->tocNode->getFiles() as $file) {
            $reference = $this->environment->resolve('doc', $file);

            if ($reference === null) {
                continue;
            }

            $url = $this->environment->relativeUrl($reference->getUrl());

            $titleLinkUtility->buildLevel($url, $reference->getTitles(), 1, $tocItems, $file);
        }

        return $this->templateRenderer->render('toc.html.twig', [
            'tocNode' => $this->tocNode,
            'tocItems' => $tocItems,
        ]);
    }
}
