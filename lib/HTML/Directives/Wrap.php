<?php

declare(strict_types=1);

namespace Gregwar\RST\HTML\Directives;

use Gregwar\RST\Nodes\Node;
use Gregwar\RST\Nodes\WrapperNode;
use Gregwar\RST\Parser;
use Gregwar\RST\SubDirective;
use function uniqid;

/**
 * Wraps a sub document in a div with a given class
 */
class Wrap extends SubDirective
{
    /** @var string */
    protected $class;

    /** @var bool */
    protected $uniqid;

    public function __construct(string $class, bool $uniqid = false)
    {
        $this->class  = $class;
        $this->uniqid = $uniqid;
    }

    public function getName() : string
    {
        return $this->class;
    }

    /**
     * @param string[] $options
     */
    public function processSub(Parser $parser, ?Node $document, string $variable, string $data, array $options) : ?Node
    {
        $class = $this->class;

        if ($this->uniqid) {
            $id = ' id="' . uniqid($this->class) . '"';
        } else {
            $id = '';
        }

        return new WrapperNode($document, '<div class="' . $class . '"' . $id . '>', '</div>');
    }
}
