<?php

declare(strict_types=1);

namespace Doctrine\RST\Builder;

use Doctrine\RST\Meta\MetaEntry;
use Doctrine\RST\Meta\Metas;
use function file_exists;
use function filectime;

class Scanner
{
    /** @var ParseQueue */
    private $parseQueue;

    /** @var Metas */
    private $metas;

    public function __construct(ParseQueue $parseQueue, Metas $metas)
    {
        $this->parseQueue = $parseQueue;
        $this->metas      = $metas;
    }

    public function scan(string $directory, string $file) : void
    {
        if ($this->parseQueue->getState($file) !== null) {
            return;
        }

        $this->parseQueue->setState($file, State::NO_PARSE);

        $entry = $this->metas->get($file);

        $rst = $directory . '/' . $file . '.rst';

        if ($entry === null || ! file_exists($rst) || $entry->getCtime() < filectime($rst)) {
            // File was never seen or changed and thus need to be parsed
            $this->parseQueue->addToParseQueue($file);
        } else {
            $this->scanMetaEntry($entry, $directory, $file);
        }
    }

    public function scanMetaEntry(MetaEntry $entry, string $directory, string $file) : void
    {
        // Have a look to the file dependencies to know if you need to parse
        // it or not
        $depends = $entry->getDepends();

        $parent = $entry->getParent();

        if ($parent !== null) {
            $depends[] = $parent;
        }

        foreach ($depends as $dependency) {
            $this->scan($directory, $dependency);

            // If any dependency needs to be parsed, this file needs also to be
            // parsed
            if ($this->parseQueue->getState($dependency) !== State::PARSE) {
                continue;
            }

            $this->parseQueue->addToParseQueue($file);
        }
    }

    public function scanMetas(string $directory) : void
    {
        $entries = $this->metas->getAll();

        foreach ($entries as $file => $infos) {
            $this->scan($directory, $file);
        }
    }
}
