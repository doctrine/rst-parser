<?php

declare(strict_types=1);

include '../../autoload.php';

use Doctrine\RST\Parser;

$content = file_get_contents('document.rst');

if ($content === false) {
    throw new Exception('Could not load file document.rst');
}

$parser   = new Parser();
$document = $parser->parse($content);

echo $document->renderDocument();
