<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Nodes;

use Doctrine\RST\Nodes\DefinitionListNode as Base;
use Doctrine\RST\Parser\DefinitionList;
use Doctrine\RST\Parser\DefinitionListTerm;
use Doctrine\RST\Span;
use function count;
use function sprintf;

class DefinitionListNode extends Base
{
    /** @var DefinitionList */
    private $definitionList;

    public function __construct(DefinitionList $definitionList)
    {
        parent::__construct();

        $this->definitionList = $definitionList;
    }

    public function render() : string
    {
        $html = '';

        $definitionListTerms = $this->definitionList->getTerms();

        foreach ($definitionListTerms as $definitionListTerm) {
            $html .= $this->renderDefinitionListTerm($definitionListTerm);
        }

        return '<dl>' . $html . '</dl>';
    }

    private function renderDefinitionListTerm(DefinitionListTerm $definitionListTerm) : string
    {
        $html = $this->renderDefinitionTerm($definitionListTerm);

        $html .= $this->renderDefinitionDescription($definitionListTerm);

        return $html;
    }

    private function renderDefinitionTerm(DefinitionListTerm $definitionListTerm) : string
    {
        $html = '';

        $classifiers = $definitionListTerm->getClassifiers();

        if ($classifiers === []) {
            $html .= sprintf('<dt>%s</dt>', $definitionListTerm->getTerm());
        } else {
            $html .= '<dt>';
            $html .= $definitionListTerm->getTerm();

            foreach ($classifiers as $classifier) {
                $html .= '<span class="classifier-delimiter">:</span> ';
                $html .= sprintf('<span class="classifier">%s</span> ', $classifier);
            }

            $html .= '</dt>';
        }

        return $html;
    }

    private function renderDefinitionDescription(DefinitionListTerm $definitionListTerm) : string
    {
        $html = '';

        $definitions    = $definitionListTerm->getDefinitions();
        $numDefinitions = count($definitions);

        if ($numDefinitions > 1) {
            $html .= $this->renderManyDefinitionDescriptions($definitions);
        } elseif ($numDefinitions === 1) {
            $html .= sprintf('<dd>%s</dd>', $definitionListTerm->getFirstDefinition());
        }

        return $html;
    }

    /**
     * @param Span[] $definitions
     */
    private function renderManyDefinitionDescriptions(array $definitions) : string
    {
        $html = '<dd>';

        foreach ($definitions as $key => $definition) {
            $first = $key === 0;
            $last  = count($definitions) - 1 === $key;

            if ($first) {
                $html .= sprintf('<p class="first">%s</p>', $definition);
            } elseif ($last) {
                $html .= sprintf('<p class="last">%s</p>', $definition);
            } else {
                $html .= sprintf('<p>%s</p>', $definition);
            }
        }

        return $html . '</dd>';
    }
}
