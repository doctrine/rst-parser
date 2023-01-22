<?php

declare(strict_types=1);

namespace Doctrine\RST\Event;

use Doctrine\Common\EventArgs;
use Doctrine\RST\Configuration;
use Doctrine\RST\Nodes\DocumentNode;

/**
 * This event is called in the ParseQueueProcessor after the file has been
 * processed.
 */
class PostProcessFileEvent extends EventArgs
{
    public const POST_PROCESS_FILE = 'postProcessFile';

    private Configuration $configuration;

    private DocumentNode $document;

    public function __construct(Configuration $configuration, DocumentNode $document)
    {
        $this->configuration = $configuration;
        $this->document      = $document;
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function getDocument(): DocumentNode
    {
        return $this->document;
    }
}
