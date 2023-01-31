<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\ContentsNode;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;
use Doctrine\RST\Utility\TitleLinkUtility;

use function count;
use function is_array;

final class ContentsNodeRenderer implements NodeRenderer
{
    private Environment $environment;

    private ContentsNode $contentsNode;

    private TemplateRenderer $templateRenderer;

    public function __construct(Environment $environment, ContentsNode $contentsNode, TemplateRenderer $templateRenderer)
    {
        $this->environment      = $environment;
        $this->contentsNode     = $contentsNode;
        $this->templateRenderer = $templateRenderer;
    }

    public function render(): string
    {
        $options = $this->contentsNode->getOptions();

        if (isset($options['hidden'])) {
            return '';
        }

        $titles = $this->contentsNode->getDocumentNode()->getTitles();
        if (count($titles) < 1 || count($titles[0]) < 2 || ! is_array($titles[0][1])) {
            // There are no subtitles to render here
            return '';
        }

        $subtitles        =  $titles[0][1];
        $maxDepth         = $this->contentsNode->getDepth();
        $titleLinkUtility = new TitleLinkUtility($this->environment, $maxDepth);

        $contentsItems = [];
        $titleLinkUtility->buildLevel('', $subtitles, 1, $contentsItems, '');

        return $this->templateRenderer->render('contents.html.twig', [
            'contentsNode' => $this->contentsNode,
            'contentsItems' => $contentsItems,
        ]);
    }
}
