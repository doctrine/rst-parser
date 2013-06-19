<?php

namespace Gregwar\RST;

use Gregwar\RST\Nodes\Node;
use Gregwar\RST\Nodes\CodeNode;
use Gregwar\RST\Nodes\QuoteNode;
use Gregwar\RST\Nodes\TitleNode;
use Gregwar\RST\Nodes\ListNode;
use Gregwar\RST\Nodes\SeparatorNode;

class Parser
{
    public static $letters = array(
        '=' => 1,
        '-' => 2,
        '*' => 3,
        '~' => 4
    );

    protected $node;
    protected $document;
    protected $buffer;
    protected $specialLevel;
    protected $directive = false;
    protected $isBlock = false;
    protected $isCode = false;

    protected function prepareCode()
    {
        if (!$this->buffer) {
            return false;
        }

        $lastLine = trim($this->buffer[count($this->buffer)-1]);

        if (strlen($lastLine) >= 2) {
            return substr($lastLine, -2) == '::';
        } else {
            return false;
        }
    }

    protected function init()
    {
        $this->isBlock = false;
        $this->specialLevel = 0;
        $this->isCode = $this->prepareCode();
        $this->buffer = array();
    }

    protected function isSpecialLine($line)
    {
        if (strlen($line) < 3) {
            return false;
        }

        $letter = $line[0];

        if (!isset(self::$letters[$letter])) {
            return false;
        }

        for ($i=1; $i<strlen($line); $i++) {
            if ($line[$i] != $letter) {
                return false;
            }
        }

        return self::$letters[$letter];
    }

    /**
     * Parses a list line
     */
    protected function parseListLine($line)
    {
        $depth = 0;
        for ($i=0; $i<strlen($line); $i++) {
            $char = $line[$i];

            if ($char == ' ') {
                $depth++;
            } else if ($char == "\t") {
                $depth += 2;
            } else {
                break;
            }
        }

        if (preg_match('/^((\*)|([\d]+)\.) /', trim($line))) {
            return array($line[$i] == '*' ? false : true,
                $depth);
        }

        return false;
    }

    /**
     * Is the current block a list ?
     */
    protected function isList()
    {
        if (!$this->buffer) {
            return false;
        }

        return $this->parseListLine($this->buffer[0]);
    }

    /**
     * A line is a code line if it's empty or if it begins with
     * a trimable caracter
     */
    protected function isBlockLine($line)
    {
        if (strlen($line)) {
            return !trim($line[0]);
        } else {
            return !trim($line);
        }
    }

    /**
     * Get current directive
     */
    protected function getDirective()
    {
        if (!$this->buffer) {
            return false;
        }

        if (preg_match('/^\.\. (\[(.+)\] |)(.+):: (.*)$/mUsi', $this->buffer[0], $match)) {
            $directive = array(
                'variable' => $match[2],
                'name' => $match[3],
                'data' => $match[4],
                'options' => array()
            );

            for ($i=1; $i<count($this->buffer); $i++) {
                if (preg_match('/^([ ]+):(.+): (.+)$/mUsi', $this->buffer[$i], $match)) {
                    $directive['options'][$match[2]] = $match[3];
                } else {
                    return false;
                }
            }

            return $directive;
        }

        return false;
    }

    /**
     * Flushes the current node
     */
    protected function flush()
    {
        $node = null;
        $directive = null;

        if ($this->buffer) {
            if ($this->specialLevel) {
                $data = implode("\n", $this->buffer);
                if ($data) {
                    $node = new TitleNode($data, $this->specialLevel);
                } else {
                    $node = new SeparatorNode;
                }
            } else if ($this->isBlock) {
                if ($this->isCode) {
                    $node = new CodeNode(implode("\n", $this->buffer));
                } else {
                    $node = new QuoteNode(implode("\n", $this->buffer));
                }
            } else {
                if ($this->isList()) {
                    $node = new ListNode();
                    $lineInfo = null;
                    $listLine = array();
                    foreach ($this->buffer as $line) {
                        $infos = $this->parseListLine($line);
                        if ($infos) {
                            if ($listLine) {
                                $node->addLine($this->parseSpan($listLine), $lineInfo[0], $lineInfo[1]);
                            }
                            $listLine = array(preg_replace('/^((\*)|([\d]+\.)) /', '', trim($line)));
                            $lineInfo = $infos;
                        } else {
                            $listLine[] = $line;
                        }
                    }
                    if ($listLine) {
                        $node->addLine($this->parseSpan($listLine), $lineInfo[0], $lineInfo[1]);
                    }
                    $node->close();
                } else {
                    $directive = $this->getDirective();
                    if (!$directive) {
                        $node = new Node($this->parseSpan($this->buffer));
                    }
                }
            }
        }

        if ($this->directive) {
        //    throw new \Exception('Unknown directive: '.$this->directive['name']);
        }

        $this->directive = $directive;

        if ($node) {
            $this->document->addNode($node);
        }
        
        $this->init();
    }

    /**
     * Process one line
     */
    protected function parseLine(&$line)
    {
        if ($this->isBlockLine($line)) {
            if (!$this->buffer && trim($line)) {
                $this->isBlock = true;
            }
        } else {
            if ($this->isBlock) {
                $this->flush();
            }
        }

        if (!$this->isBlock) {
            if (!trim($line)) {
                $this->flush();
            } else {
                $specialLevel = $this->isSpecialLine($line);

                if ($specialLevel) {
                    $lastLine = array_pop($this->buffer);
                    $this->flush();

                    $this->specialLevel = $specialLevel;
                    $this->buffer = array($lastLine);
                    $this->flush();
                } else {
                    $this->buffer[] = $line;
                }
            }
        } else {
            $this->buffer[] = $line;
        }
    }

    /**
     * Process all the lines of a document string
     */
    protected function parseLines(&$document)
    {
        $lines = explode("\n", $document);

        foreach ($lines as $line) {
            $this->parseLine($line);
        }
        
        $this->flush();
        $this->flush();
    }

    /**
     * Parse a document and return a Document instance
     */
    public function parse(&$document)
    {
        $this->document = new Document;
        $this->init();
        $this->parseLines(trim($document));

        return $this->document;
    }

    /**
     * Parses a span, this will apply emphasis, references etc.
     */
    public function parseSpan($span)
    {
        if (is_array($span)) {
            $span = implode("\n", $span);
        }

        $prefix = sha1(time().'/'.mt_rand());
        $tokens = array();
        $span = preg_replace_callback('/`(.+)`/mUsi', function($match) use (&$tokens, $prefix) {
            $id = $prefix.count($tokens);
            $tokens[$id] = '<code>'.htmlspecialchars($match[1]).'</code>';

            return $id;
        }, $span);
        $span = preg_replace('/\*\*(.+)\*\*/mUsi', '<b>$1</b>', $span);
        $span = preg_replace('/\*(.+)\*/mUsi', '<em>$1</em>', $span);
        $span = preg_replace('/_(.+)_/mUsi', '<u>$1</u>', $span);

        foreach ($tokens as $id => $value) {
            $span = str_replace($id, $value, $span);
        }

        return $span;
    }
}