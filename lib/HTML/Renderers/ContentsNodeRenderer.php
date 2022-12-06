<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\ContentsNode;
use Doctrine\RST\Nodes\TocNode;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

use function count;
use function is_array;

final class ContentsNodeRenderer implements NodeRenderer
{
    /** @var Environment */
    private $environment;

    /** @var ContentsNode */
    private $contentsNode;

    /** @var TemplateRenderer */
    private $templateRenderer;

    public function __construct(Environment $environment, ContentsNode $contentsNode, TemplateRenderer $templateRenderer)
    {
        $this->environment      = $environment;
        $this->contentsNode          = $contentsNode;
        $this->templateRenderer = $templateRenderer;
    }

    public function render(): string
    {
        $options = $this->contentsNode->getOptions();

        if (isset($options['hidden'])) {
            return '';
        }

        return $this->templateRenderer->render('contents.html.twig', [
            'contentsNode' => $this->contentsNode
        ]);
    }
}
