<?php

namespace Gregwar\RST\HTML;

use Gregwar\RST\Span as Base;

class Span extends Base
{
    public function emphasis($text)
    {
        return '<em>'.$text.'</em>';
    }

    public function strongEmphasis($text)
    {
        return '<strong>'.$text.'</strong>';
    }

    public function nbsp()
    {
        return '&nbsp;';
    }

    public function br()
    {
        return '<br />';
    }

    public function literal($text)
    {
        return '<code>'.$text.'</code>';
    }

    public function link($url, $title)
    {
        return '<a href="'.htmlspecialchars($url).'">'.$title.'</a>';
    }

    public function escape($span)
    {
        return htmlspecialchars($span);
    }

    public function reference($reference, $value)
    {
        $text = $value['text'] ?: (isset($reference['title']) ? $reference['title'] : '');
        $text = trim($text);

        // reference to another document
        if ($reference['url']) {
            $url = $reference['url'];
            if ($value['anchor']) {
                $url .= '#' . $value['anchor'];
            }
            $link = $this->link($url, $text);

        // reference to anchor in existing document
        } elseif ($value['url']) {
            $url = $this->environment->getLink($value['url']);

            $link = $this->link($url, $text);
        } else {
            $link = $this->link('#', $text.' (unresolved reference)');
        }

        return $link;
    }
}
