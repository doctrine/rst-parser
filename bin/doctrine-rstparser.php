<?php

declare (strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Doctrine\RST\Command\RenderCommand;
use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new RenderCommand());

$application->run();
