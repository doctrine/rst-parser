<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Nodes;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\TocNode as Base;
use function is_array;
use function str_replace;

class TocNode extends Base
{
    /** @var int */
    private $depth;

    public function render() : string
    {
        if (isset($this->options['hidden'])) {
            return '';
        }

        $this->depth = (int) ($this->options['depth'] ?? 2);

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
        if ($level > $this->depth) {
            return '';
        }

        $html = '';
        foreach ($titles as $k => $entry) {
            $path[$level - 1] = (int) $k + 1;

            list($title, $childs) = $entry;

            $slug = $title;

            if (is_array($title)) {
                $slug = $title[1];
            }

            $anchor = Environment::slugify($slug);
            $target = $url . '#' . $anchor;

            if (is_array($title)) {
                list($title, $target) = $title;

                $info = $this->environment->resolve('doc', $target);

                $target = $this->environment->relativeUrl($info->getUrl());
            }

            $id = str_replace('../', '', (string) $target);
            $id = str_replace(['#', '.', '/'], '-', $id);

            $html .= '<li id="' . $id . '" class="toc-item"><a href="' . $target . '">' . $title . '</a></li>';

            if (! $childs) {
                continue;
            }

            $html .= '<ul>';
            $html .= $this->renderLevel($url, $childs, $level + 1, $path);
            $html .= '</ul>';
        }

        return $html;
    }
}
