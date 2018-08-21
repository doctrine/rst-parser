<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML;

use Doctrine\RST\Span as Base;
use function htmlspecialchars;
use function trim;

class Span extends Base
{
    public function emphasis(string $text) : string
    {
        return '<em>' . $text . '</em>';
    }

    public function strongEmphasis(string $text) : string
    {
        return '<strong>' . $text . '</strong>';
    }

    public function nbsp() : string
    {
        return '&nbsp;';
    }

    public function br() : string
    {
        return '<br />';
    }

    public function literal(string $text) : string
    {
        return '<code>' . $text . '</code>';
    }

    public function link(string $url, string $title) : string
    {
        return '<a href="' . htmlspecialchars($url) . '">' . $title . '</a>';
    }

    public function escape(string $span) : string
    {
        return htmlspecialchars($span);
    }

    /**
     * @param string[] $reference
     * @param string[] $value
     */
    public function reference(array $reference, array $value) : string
    {
        $text = $value['text'] ?: ($reference['title'] ?? '');
        $text = trim($text);

        // reference to another document
        if ($reference['url'] !== null) {
            $url = $reference['url'];

            if ($value['anchor'] !== null) {
                $url .= '#' . $value['anchor'];
            }
            $link = $this->link($url, $text);

        // reference to anchor in existing document
        } elseif ($value['url'] !== null) {
            $url = $this->environment->getLink($value['url']);

            $link = $this->link($url, $text);
        } else {
            $link = $this->link('#', $text . ' (unresolved reference)');
        }

        return $link;
    }
}
