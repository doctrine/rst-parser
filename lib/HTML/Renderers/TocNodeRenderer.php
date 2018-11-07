<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\TocNode;
use Doctrine\RST\Renderers\NodeRenderer;
use function count;
use function is_array;

class TocNodeRenderer implements NodeRenderer
{
    /** @var Environment */
    private $environment;

    /** @var TocNode */
    private $tocNode;

    /** @var int */
    private $depth;

    public function __construct(Environment $environment, TocNode $tocNode)
    {
        $this->environment = $environment;
        $this->tocNode     = $tocNode;
        $this->depth       = $this->tocNode->getDepth();
    }

    public function render() : string
    {
        $options = $this->tocNode->getOptions();

        if (isset($options['hidden'])) {
            return '';
        }

        $html = '<div class="toc"><ul>';

        foreach ($this->tocNode->getFiles() as $file) {
            $reference = $this->environment->resolve('doc', $file);

            if ($reference === null) {
                continue;
            }

            $url = $this->environment->relativeUrl($reference->getUrl());

            $html .= $this->renderLevel($url, $reference->getTitles());
        }

        $html .= '</ul></div>';

        return $html;
    }

    /**
     * @param mixed[]|array $titles
     * @param mixed[]       $path
     */
    private function renderLevel(
        ?string $url,
        array $titles,
        int $level = 1,
        array $path = []
    ) : string {
        $html = '';

        foreach ($titles as $k => $entry) {
            $path[$level - 1] = (int) $k + 1;

            [$title, $children] = $entry;

            [$title, $target] = $this->generateTarget($url, $title);

            $html .= '<li id="' . $this->generateTargetId($target) . '" class="toc-item">';

            $html .= '<a href="' . $this->environment->generateUrl($target) . '">' . $title . '</a>';

            // render children until we hit the configured maxdepth
            if (count($children) > 0 && $level < $this->depth) {
                $html .= '<ul>';
                $html .= $this->renderLevel($url, $children, $level + 1, $path);
                $html .= '</ul>';
            }

            $html .= '</li>';
        }

        return $html;
    }

    private function generateTargetId(string $target) : string
    {
        return Environment::slugify($target);
    }

    /**
     * @param string[]|string $title
     *
     * @return mixed[]
     */
    private function generateTarget(?string $url, $title) : array
    {
        $anchor = $this->generateAnchorFromTitle($title);

        $target = $url . '#' . $anchor;

        if (is_array($title)) {
            [$title, $target] = $title;

            $reference = $this->environment->resolve('doc', $target);

            if ($reference === null) {
                return [$title, $target];
            }

            $target = $this->environment->relativeUrl($reference->getUrl());
        }

        return [$title, $target];
    }

    /**
     * @param string[]|string $title
     */
    private function generateAnchorFromTitle($title) : string
    {
        $slug = is_array($title)
            ? $title[1]
            : $title;

        return Environment::slugify($slug);
    }
}
