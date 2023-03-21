<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\TocNode;
use Doctrine\RST\References\Resolver;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;
use Doctrine\RST\Utility\TitleLinkUtility;

use function array_key_last;
use function count;

final class TocNodeRenderer implements NodeRenderer
{
    private Environment $environment;

    private TocNode $tocNode;

    private TemplateRenderer $templateRenderer;
    private Resolver $resolver;

    public function __construct(Environment $environment, TocNode $tocNode, TemplateRenderer $templateRenderer)
    {
        $this->environment      = $environment;
        $this->tocNode          = $tocNode;
        $this->templateRenderer = $templateRenderer;
        $this->resolver         = new Resolver();
    }

    public function render(): string
    {
        $options = $this->tocNode->getOptions();

        if (isset($options['hidden'])) {
            return '';
        }

        $maxDepth = (int) ($options['maxdepth'] ?? 0);

        $tocItems = [];

        $titleLinkUtility = new TitleLinkUtility($this->environment, $this->tocNode->isTitlesOnly() ? 1 : $this->tocNode->getDepth());

        foreach ($this->tocNode->getFiles() as $file) {
            $reference = $this->resolver->resolve($this->environment, 'doc', $file);

            if ($reference === null) {
                continue;
            }

            $url = $this->environment->relativeUrl($reference->getUrl());

            $titleLinkUtility->buildLevel($url, $reference->getTitles(), 1, $tocItems, $file);
            if ($maxDepth === 1) {
                continue;
            }

            $childTocItems = $this->buildSubPages($file, $titleLinkUtility, 1, $maxDepth);
            if (count($tocItems) <= 0) {
                continue;
            }

            $tocItems[array_key_last($tocItems)]['subpages'] = $childTocItems;
        }

        return $this->templateRenderer->render('toc.html.twig', [
            'tocNode' => $this->tocNode,
            'tocItems' => $tocItems,
        ]);
    }

    /** @return mixed[] */
    private function buildSubPages(string $file, TitleLinkUtility $titleLinkUtility, int $currentLevel, int $maxDepth): array
    {
        $metas    = $this->environment->getMetas();
        $metaData = $metas->get($file);
        $tocItems = [];
        if ($metaData !== null) {
            foreach ($metaData->getChildDocuments() as $childDocument) {
                $reference = $this->resolver->resolve($this->environment, 'doc', $childDocument->getFile());
                $titleLinkUtility->buildLevel($childDocument->getUrl(), $reference->getTitles(), 1, $tocItems, $childDocument->getFile());
                if ($maxDepth !== 0 && $maxDepth <= $currentLevel + 1) {
                    continue;
                }

                $childTocItems                                   = $this->buildSubPages($childDocument->getFile(), $titleLinkUtility, $currentLevel + 1, $maxDepth);
                $tocItems[array_key_last($tocItems)]['subpages'] = $childTocItems;
            }
        }

        return $tocItems;
    }
}
