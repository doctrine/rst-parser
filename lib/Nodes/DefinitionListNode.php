<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

use Doctrine\RST\Parser\DefinitionList;

final class DefinitionListNode extends Node
{
    /** @var DefinitionList */
    private $definitionList;

    public function __construct(DefinitionList $definitionList)
    {
        parent::__construct();

        $this->definitionList = $definitionList;
    }

    public function getDefinitionList(): DefinitionList
    {
        return $this->definitionList;
    }
}
