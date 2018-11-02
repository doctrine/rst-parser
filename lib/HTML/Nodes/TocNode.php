<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Nodes;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\TocNode as Base;
use function count;
use function is_array;

class TocNode extends Base
{
    private const DEFAULT_DEPTH = 2;

    /** @var int */
    private $depth;

    public function render() : string
    {
        if (isset($this->options['hidden'])) {
            return '';
        }

        $this->depth = $this->getDepth();

        $html = '<div class="toc"><ul>';

        foreach ($this->files as $file) {
            $reference = $this->environment->resolve('doc', $file);

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

            $html .= '<a href="' . $target . '">' . $title . '</a>';

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

            $info = $this->environment->resolve('doc', $target);

            $target = $this->environment->relativeUrl($info->getUrl());
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

    private function getDepth() : int
    {
        if (isset($this->options['depth'])) {
            return (int) $this->options['depth'];
        }

        if (isset($this->options['maxdepth'])) {
            return (int) $this->options['maxdepth'];
        }

        return self::DEFAULT_DEPTH;
    }
}
