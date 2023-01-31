<?php

declare(strict_types=1);

namespace Doctrine\RST\Event;

use Doctrine\Common\EventArgs;
use Doctrine\RST\Nodes\DocumentNode;

final class PostParseDocumentEvent extends EventArgs
{
    public const POST_PARSE_DOCUMENT = 'postParseDocument';

    private DocumentNode $documentNode;

    public function __construct(DocumentNode $documentNode)
    {
        $this->documentNode = $documentNode;
    }

    public function getDocumentNode(): DocumentNode
    {
        return $this->documentNode;
    }
}
