<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Renderers;

use Doctrine\RST\References\ResolvedReference;
use Doctrine\RST\Renderers\SpanNodeRenderer as BaseSpanNodeRenderer;
use function is_string;
use function substr;
use function trim;

class SpanNodeRenderer extends BaseSpanNodeRenderer
{
    public function emphasis(string $text) : string
    {
        return '\textit{' . $text . '}';
    }

    public function strongEmphasis(string $text) : string
    {
        return '\textbf{' . $text . '}';
    }

    public function nbsp() : string
    {
        return '~';
    }

    public function br() : string
    {
        return "\\\\\\\\\n";
    }

    public function literal(string $text) : string
    {
        return '\verb|' . $text . '|';
    }

    /**
     * @param mixed[] $attributes
     */
    public function link(?string $url, string $title, array $attributes = []) : string
    {
        if (is_string($url) && $url !== '' && $url[0] === '#') {
            $url = substr($url, 1);
            $url = $url !== '' ? '#' . $url : '';

            return '\ref{' . $this->environment->getUrl() . $url . '}';
        }

        return '\href{' . $url . '}{' . $title . '}';
    }

    public function escape(string $span) : string
    {
        return $span;
    }

    /**
     * @param mixed[] $value
     */
    public function reference(ResolvedReference $reference, array $value) : string
    {
        $text = $value['text'] ?: $reference->getTitle();
        $url  = $reference->getUrl();

        if ($value['anchor'] !== '') {
            $url .= $value['anchor'];
        }

        if ($text === null) {
            $text = '';
        }

        if ($url === null) {
            $url = '';
        }

        return $this->link($url, trim($text));
    }
}
