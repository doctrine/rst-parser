<?php

declare(strict_types=1);

namespace Doctrine\RST\TextRoles;

class CodeRole extends WrapperTextRole
{
    public function __construct()
    {
        parent::__construct('code');
    }
}
