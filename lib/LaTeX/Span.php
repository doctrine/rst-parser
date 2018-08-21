<?php

declare(strict_types=1);

namespace Gregwar\RST\LaTeX;

use Gregwar\RST\Span as Base;
use function substr;
use function trim;

class Span extends Base
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

    public function link(string $url, string $title, string $refDoc = '') : string
    {
        if ($url !== '' && $url[0] === '#') {
            if ($refDoc === '') {
                $refDoc = $this->environment->getUrl();
            }

            $url = substr($url, 1);
            $url = $url !== '' ? '#' . $url : '';

            return '\ref{' . $refDoc . $url . '}';
        }

        return '\href{' . $url . '}{' . $title . '}';
    }

    public function escape(string $span) : string
    {
        return $span;
    }

    /**
     * @param string[] $reference
     * @param string[] $value
     */
    public function reference(array $reference, array $value) : string
    {
        if ($reference !== []) {
            $file   = $reference['file'];
            $text   = $value['text'] ?: ($reference['title'] ?? '');
            $refDoc = $file;
            $url    = '#';

            if ($value['anchor'] !== '') {
                $url .= $value['anchor'];
            }

            $link = $this->link($url, trim($text), $refDoc);
        } else {
            $link = $this->link('#', '(unresolved reference)');
        }

        return $link;
    }
}
