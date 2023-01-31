<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Renderers;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\TocNode;
use Doctrine\RST\References\Resolver;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

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
        $tocItems = [];
        $resolver = new Resolver();

        foreach ($this->tocNode->getFiles() as $file) {
            $reference = $resolver->resolve($this->environment, 'doc', $file);

            if ($reference === null) {
                continue;
            }

            $url = $this->environment->relativeUrl($reference->getUrl());

            $tocItems[] = ['url' => $url];
        }

        return $this->templateRenderer->render('toc.tex.twig', [
            'tocNode' => $this->tocNode,
            'tocItems' => $tocItems,
        ]);
    }
}
