<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Nodes\DefinitionListNode;
use Doctrine\RST\Nodes\SpanNode;
use Doctrine\RST\Parser\DefinitionList;
use Doctrine\RST\Parser\DefinitionListTerm;
use Doctrine\RST\Renderers\NodeRenderer;
use function count;
use function sprintf;

class DefinitionListNodeRenderer implements NodeRenderer
{
    /** @var DefinitionList */
    private $definitionList;

    public function __construct(DefinitionListNode $definitionListNode)
    {
        $this->definitionList = $definitionListNode->getDefinitionList();
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
            $html .= sprintf('<dt>%s</dt>', $definitionListTerm->getTerm()->render());
        } else {
            $html .= '<dt>';
            $html .= $definitionListTerm->getTerm()->render();

            foreach ($classifiers as $classifier) {
                $html .= '<span class="classifier-delimiter">:</span> ';
                $html .= sprintf('<span class="classifier">%s</span> ', $classifier->render());
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
            $html .= sprintf('<dd>%s</dd>', $definitionListTerm->getFirstDefinition()->render());
        }

        return $html;
    }

    /**
     * @param SpanNode[] $definitions
     */
    private function renderManyDefinitionDescriptions(array $definitions) : string
    {
        $html = '<dd>';

        foreach ($definitions as $key => $definition) {
            $first = $key === 0;
            $last  = count($definitions) - 1 === $key;

            if ($first) {
                $html .= sprintf('<p class="first">%s</p>', $definition->render());
            } elseif ($last) {
                $html .= sprintf('<p class="last">%s</p>', $definition->render());
            } else {
                $html .= sprintf('<p>%s</p>', $definition->render());
            }
        }

        return $html . '</dd>';
    }
}
