<?php

declare(strict_types=1);

namespace Gregwar\RST;

class ErrorManager
{
    protected $abort = true;

    public function abortOnError($abort) : void
    {
        $this->abort = $abort;
    }

    public function error($message) : void
    {
        if ($this->abort) {
            throw new \Exception($message);
        } else {
            echo '/!\\ ' . $message . "\n";
        }
    }
}
