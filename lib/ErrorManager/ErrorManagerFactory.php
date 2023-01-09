<?php

declare(strict_types=1);

namespace Doctrine\RST\ErrorManager;

use Doctrine\RST\ErrorManager;

interface ErrorManagerFactory
{
    public function getErrorManager(): ErrorManager;
}
