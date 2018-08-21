<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Directives;

use Doctrine\RST\HTML\Nodes\FigureNode;
use Doctrine\RST\HTML\Nodes\ImageNode;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;
use Doctrine\RST\SubDirective;

/**
 * Renders an image, example :
 *
 * .. figure:: image.jpg
 *      :width: 100
 *      :title: An image
 *
 *      Here is an awesome caption
 *
 */
class Figure extends SubDirective
{
    public function getName() : string
    {
        return 'figure';
    }

    /**
     * @param string[] $options
     */
    public function processSub(Parser $parser, ?Node $document, string $variable, string $data, array $options) : ?Node
    {
        $environment = $parser->getEnvironment();
        $url         = $environment->relativeUrl($data);

        return new FigureNode(new ImageNode($url, $options), $document);
    }
}
