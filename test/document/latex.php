<?php

declare(strict_types=1);

include '../../autoload.php';

use Gregwar\RST\LaTeX\Kernel;
use Gregwar\RST\Parser;

$parser   = new Parser(null, new Kernel());
$document = $parser->parse(file_get_contents('document.rst'));

echo $document->renderDocument();
