<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\TocNode;
use Doctrine\RST\References\Resolver;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;
use Doctrine\RST\Utility\TitleLinkUtility;

final class TocNodeRenderer implements NodeRenderer
{
    private Environment $environment;

    private TocNode $tocNode;

    private TemplateRenderer $templateRenderer;

    public function __construct(Environment $environment, TocNode $tocNode, TemplateRenderer $templateRenderer)
    {
        $this->environment      = $environment;
        $this->tocNode          = $tocNode;
        $this->templateRenderer = $templateRenderer;
    }

    public function render(): string
    {
        $options  = $this->tocNode->getOptions();
        $resolver = new Resolver();

        if (isset($options['hidden'])) {
            return '';
        }

        $tocItems = [];

        $titleLinkUtility = new TitleLinkUtility($this->environment, $this->tocNode->isTitlesOnly() ? 1 : $this->tocNode->getDepth());

        foreach ($this->tocNode->getFiles() as $file) {
            $reference = $resolver->resolve($this->environment, 'doc', $file);

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
