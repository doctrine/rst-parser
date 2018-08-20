<?php

declare(strict_types=1);

namespace Gregwar\RST\HTML\Directives;

use Gregwar\RST\HTML\Nodes\FigureNode;
use Gregwar\RST\HTML\Nodes\ImageNode;
use Gregwar\RST\Parser;
use Gregwar\RST\SubDirective;

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

    public function processSub(Parser $parser, $document, $variable, $data, array $options)
    {
        $environment = $parser->getEnvironment();
        $url         = $environment->relativeUrl($data);

        return new FigureNode(new ImageNode($url, $options), $document);
    }
}
