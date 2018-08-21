<?php

declare(strict_types=1);

include '../../autoload.php';

use Doctrine\RST\LaTeX\Kernel;
use Doctrine\RST\Parser;

$parser   = new Parser(null, new Kernel());
$document = $parser->parse(file_get_contents('document.rst'));

echo $document->renderDocument();
