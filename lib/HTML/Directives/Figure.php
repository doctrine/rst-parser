<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Directives;

use Doctrine\RST\Directives\SubDirective;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;
use Exception;

use function explode;
use function sprintf;
use function strpos;

/**
 * Renders an image, example :
 *
 * .. figure:: image.jpg
 *      :width: 100
 *      :title: An image
 *
 *      Here is an awesome caption
 */
final class Figure extends SubDirective
{
    public function getName(): string
    {
        return 'figure';
    }

    /** @param string[] $options */
    public function processSub(
        Parser $parser,
        ?Node $document,
        string $variable,
        string $data,
        array $options
    ): ?Node {
        $environment = $parser->getEnvironment();

        $url = $environment->relativeUrl($data);

        if ($url === null) {
            throw new Exception(sprintf('Could not get relative url for %s', $data));
        }

        $figureOptions = [];
        foreach ($options as $name => $value) {
            if (strpos($name, 'fig') !== 0) {
                continue;
            }

            $figureOptions[$name] = $value;
            unset($options[$name]);
        }

        $nodeFactory = $parser->getNodeFactory();
        $figureNode  = $nodeFactory->createFigureNode(
            $nodeFactory->createImageNode($url, $options),
            $document
        );

        if (isset($figureOptions['figclass'])) {
            $figureNode->setClasses(explode(' ', $figureOptions['figclass']));
        }

        if (isset($figureOptions['figwidth'])) {
            $figureNode->setWidth($figureOptions['figwidth']);
        }

        return $figureNode;
    }
}
