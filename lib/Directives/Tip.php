<?php

declare(strict_types=1);

namespace Doctrine\RST\Directives;

class Tip extends Admonition
{
    public function __construct()
    {
        parent::__construct('tip', 'bg-success', 'text-light', 'fas fa-question-circle');
    }
}
