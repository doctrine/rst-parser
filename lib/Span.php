<?php

declare(strict_types=1);

namespace Doctrine\RST;

use Doctrine\RST\Nodes\Node;
use function htmlspecialchars;
use function implode;
use function is_array;
use function mt_rand;
use function preg_match;
use function preg_match_all;
use function preg_replace;
use function preg_replace_callback;
use function sha1;
use function str_replace;
use function time;
use function trim;

abstract class Span extends Node
{
    /** @var Parser */
    protected $parser;

    /** @var string */
    protected $span;

    /** @var mixed[] */
    protected $tokens;

    /** @var Environment */
    protected $environment;

    /**
     * @param string|string[] $span
     */
    public function __construct(Parser $parser, $span)
    {
        parent::__construct();

        if (is_array($span)) {
            $span = implode("\n", $span);
        }

        $tokenId   = 0;
        $prefix    = mt_rand() . '|' . time();
        $generator = function () use ($prefix, &$tokenId) {
            $tokenId++;
            return sha1($prefix . '|' . $tokenId);
        };

        // Replacing literal with tokens
        $tokens = [];
        $span   = preg_replace_callback(
            '/``(.+)``(?!`)/mUsi',
            function ($match) use (&$tokens, $generator) {
                $id          = $generator();
                $tokens[$id] = [
                    'type' => 'literal',
                    'text' => htmlspecialchars($match[1]),
                ];

                return $id;
            },
            $span
        );

        $environment       = $parser->getEnvironment();
        $this->environment = $environment;

        // Replacing numbering
        foreach ($environment->getTitleLetters() as $level => $letter) {
            $span = preg_replace_callback('/\#\\' . $letter . '/mUsi', function ($match) use ($environment, $level) {
                return $environment->getNumber($level);
            }, $span);
        }

        // Signaling anonymous names
        $environment->resetAnonymousStack();

        if (preg_match_all('/(([a-z0-9]+)|(`(.+)`))__/mUsi', $span, $matches) > 0) {
            foreach ($matches[2] as $k => $y) {
                $name = $matches[2][$k] ?: $matches[4][$k];
                $environment->pushAnonymous($name);
            }
        }

        // Looking for references to other documents
        $span = preg_replace_callback('/:([a-z0-9]+):`(.+)`/mUsi', function ($match) use (&$environment, $generator, &$tokens) {
            $section = $match[1];
            $url     = $match[2];
            $id      = $generator();
            $anchor  = null;

            $text = null;
            if (preg_match('/^(.+)<(.+)>$/mUsi', $url, $match) > 0) {
                $text = $match[1];
                $url  = $match[2];
            }

            if (preg_match('/^(.+)#(.+)$/mUsi', $url, $match) > 0) {
                $url    = $match[1];
                $anchor = $match[2];
            }

            $tokens[$id] = [
                'type' => 'reference',
                'section' => $section,
                'url' => $url,
                'text' => $text,
                'anchor' => $anchor,
            ];

            $environment->found($section, $url);

            return $id;
        }, $span);

        // Link callback
        $linkCallback = function ($match) use ($environment, $generator, &$tokens) {
            $link = $match[2] ?: $match[4];
            $id   = $generator();
            $next = $match[5];
            $url  = null;

            if (preg_match('/^(.+) <(.+)>$/mUsi', $link, $m) > 0) {
                $link = $m[1];
                $environment->setLink($link, $m[2]);
                $url = $m[2];
            }

            // anchors to current document
            if ($url === null) {
                $anchor = Environment::slugify($link);

                $tokens[$id] = [
                    'type' => 'link',
                    'link' => $link,
                    'anchor' => $anchor,
                    'url' => '',
                ];

                return $id . $next;
            }

            $tokens[$id] = [
                'type' => 'link',
                'link' => $link,
                'url' => $url,
            ];

            return $id . $next;
        };

        // Replacing anonymous links
        $span = preg_replace_callback('/(([a-z0-9]+)|(`(.+)`))__([^a-z0-9]{1}|$)/mUsi', $linkCallback, $span);

        // Replacing links
        $span = preg_replace_callback('/(([a-z0-9]+)|(`(.+)`))_([^a-z0-9]{1}|$)/mUsi', $linkCallback, $span);

        $this->tokens = $tokens;
        $this->parser = $parser;
        $this->span   = $span;
    }

    public function process(string $data) : string
    {
        $self        = $this;
        $environment = $this->parser->getEnvironment();

        $span = $this->escape($data);

        // Emphasis
        $span = preg_replace_callback('/\*\*(.+)\*\*/mUsi', function ($matches) use ($self) {
            return $self->strongEmphasis($matches[1]);
        }, $span);
        $span = preg_replace_callback('/\*(.+)\*/mUsi', function ($matches) use ($self) {
            return $self->emphasis($matches[1]);
        }, $span);

        // Nbsp
        $span = preg_replace('/~/', $this->nbsp(), $span);

        // Replacing variables
        $span = preg_replace_callback('/\|(.+)\|/mUsi', function ($match) use ($environment) {
            return $environment->getVariable($match[1]);
        }, $span);

        // Adding brs when a space is at the end of a line
        $span = preg_replace('/ \n/', $this->br(), $span);

        return $span;
    }

    public function render() : string
    {
        $environment = $this->parser->getEnvironment();
        $span        = $this->process($this->span);

        // Replacing tokens
        foreach ($this->tokens as $id => $value) {
            switch ($value['type']) {
                case 'literal':
                    $span = str_replace($id, $this->literal($value['text']), $span);
                    break;
                case 'reference':
                    // try to resolve by url first
                    $reference = $environment->resolve($value['section'], $value['url']);

                    if ($reference !== null) {
                        $link = $this->reference($reference, $value);

                    // try to resolve by text second
                    } elseif (isset($value['text']) && $value['text'] !== null) {
                        /** @var string[] $reference */
                        $reference = $environment->resolveByText($value['section'], $value['text']);

                        // if we resolved by text set the anchor to the url
                        $value['anchor'] = $value['url'];

                        $link = $this->reference($reference, $value);
                    } else {
                        $link = '';
                    }

                    $span = str_replace($id, $link, $span);
                    break;
                case 'link':
                    if ($value['url']) {
                        if ($environment->useRelativeUrls()) {
                            $url = $environment->relativeUrl($value['url']);
                        } else {
                            $url = $value['url'];
                        }
                    } elseif ($value['anchor']) {
                        $link = $environment->getLink($value['link']);

                        if ($link !== '') {
                            $url = $link;
                        } else {
                            $url = '#' . $value['anchor'];
                        }
                    } else {
                        $url = $environment->getLink($value['link']);
                    }

                    $link = $this->link($url, $this->process($value['link']));
                    $span = str_replace($id, $link, $span);
                    break;
            }
        }

        return $span;
    }

    public function emphasis(string $text) : string
    {
        return $text;
    }

    public function strongEmphasis(string $text) : string
    {
        return $text;
    }

    public function nbsp() : string
    {
        return ' ';
    }

    public function br() : string
    {
        return "\n";
    }

    public function literal(string $text) : string
    {
        return $text;
    }

    public function link(string $url, string $title) : string
    {
        return $title . ' (' . $url . ')';
    }

    public function escape(string $span) : string
    {
        return $span;
    }

    /**
     * @param null|string[] $reference
     * @param string[]      $value
     */
    public function reference(?array $reference, array $value) : string
    {
        if ($reference !== null && $reference !== []) {
            $text = $value['text'] ?: ($reference['title'] ?? '');
            $link = $this->link($reference['url'], trim($text));
        } else {
            $link = $this->link('#', '(unresolved reference)');
        }

        return $link;
    }
}
