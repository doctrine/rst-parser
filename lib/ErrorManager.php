<?php

declare(strict_types=1);

namespace Gregwar\RST;

use Exception;

class ErrorManager
{
    /** @var bool */
    protected $abort = true;

    public function abortOnError(bool $abort) : void
    {
        $this->abort = $abort;
    }

    public function error(string $message) : void
    {
        if ($this->abort) {
            throw new Exception($message);
        }

        echo '/!\\ ' . $message . "\n";
    }
}
