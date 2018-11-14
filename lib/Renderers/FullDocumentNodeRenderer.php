<?php

declare(strict_types=1);

namespace Doctrine\RST\Renderers;

interface FullDocumentNodeRenderer
{
    public function renderDocument() : string;
}
