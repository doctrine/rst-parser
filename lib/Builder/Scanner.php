<?php

declare(strict_types=1);

namespace Doctrine\RST\Builder;

use Doctrine\RST\Meta\Metas;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Scanner
{
    private $fileExtension;

    private $directory;

    private $metas;

    private $finder;

    /** @var SplFileInfo[] */
    private $fileInfos = [];

    public function __construct(string $fileExtension, string $directory, Metas $metas, Finder $finder = null)
    {
        $this->fileExtension = $fileExtension;
        $this->directory = $directory;
        $this->metas = $metas;

        $this->finder = $finder ?: new Finder();
        $this->finder->in($this->directory)
            ->files()
            ->name('*.'.$this->fileExtension);
    }

    /**
     * Scans a directory recursively looking for all files to parse.
     *
     * This takes into account the presence of cached & fresh MetaEntry
     * objects, and avoids adding files to the parse queue that have
     * not changed and whose dependencies have not changed.
     */
    public function scan() : ParseQueue
    {
        // completely populate the splFileInfos property
        $this->fileInfos = [];
        foreach ($this->finder as $fileInfo) {
            $relativeFilename = $fileInfo->getRelativePathname();
            // strip off the extension
            $documentPath = substr($relativeFilename, 0, -(strlen($this->fileExtension) + 1));

            $this->fileInfos[$documentPath] = $fileInfo;
        }

        $parseQueue = new ParseQueue();
        foreach ($this->fileInfos as $filename => $fileInfo) {
            $parseQueue->addFile(
                $filename,
                $this->doesFileRequireParsing($filename, $parseQueue)
            );
        }

        return $parseQueue;

        /*
         * 1) See if meta exists. If it does not, add it to ParseQueue
         *      as needing to PARSE_NEEDED
         * 2) If meta DOES exist
         *      a) if stale, PARSE_NEEDED
         *      b) if not stale, loop over each dependency and
         *          do the exact same check
         *          -> how can we avoid circular issues?
         *              -> basically, each iteration of the loop
         *                  going deeper just needs to know that
         *                  if it hits its parent caller/file, it
         *                  should take no information from this,
         *                  but not try to follow its dependencies.
         *              -> could possibly do this with a flag called
         *                  PARSE_PROCESSING, which is released after
         *                  you follow all of your dependencies. If
         *                  you hit a PARSE_PROCESSING on one of your
         *                  dependencies, you just exist and re-set
         *                  your status back to some unknown. If you
         *                  ARE able to determine from your dependencies
         *                  if you need to be parsed, then you set to whatever
         *                  that status is.
         *              -> this probably means adding everything to the
         *                  ParseQueue in the beginning. And maybe this
         *                  just becomes a utility class of the Scanner,
         *                  and an array of filenames or array of SourceDocument
         *                  objects is returned. Maybe Scanner can be more
         *                  stateless, and a lot of the recursive logic is
         *                  moved into this temporary, stateful ParseQueue.
         *          i) if no dependencies need to be re-parsed, don't parse
         *          ii) if any need re-parsing, re-parse
         *
         * We will use ParseQueue in an intelligent way - actually asking
         * it if it is aware of a file yet, instead of using all this getState()
         * garbage
         */
    }

    private function doesFileRequireParsing(string $filename, ParseQueue $parseQueue, array $filesAlreadyBeingChecked = []) : bool
    {
        if (!isset($this->fileInfos[$filename])) {
            throw new \InvalidArgumentException(sprintf('No file info found for "%s" - file does not exist.', $filename));
        }

        $file = $this->fileInfos[$filename];

        $documentFilename = $this->getFilenameFromFile($file);
        $entry = $this->metas->get($documentFilename);

        if ($entry === null || $entry->getCtime() < $file->getCTime()) {
            // File is new or changed and thus need to be parsed
            return true;
        }

        // Look to the file's dependencies to know if you need to parse it or not
        $dependencies = $entry->getDepends();

        $parent = $entry->getParent();
        if ($parent !== null) {
            $dependencies[] = $parent;
        }

        $filesAlreadyBeingChecked[] = $documentFilename;

        foreach ($dependencies as $dependency) {
            if (in_array($dependency, $filesAlreadyBeingChecked, true)) {
                /*
                 * File is already being checked. For example, consider
                 * this dependency tree:
                 *
                 *      DocA (depends on)->
                 *          DocB (depends on)->
                 *              DocC (depends on)-> DocB & DocD
                 *
                 * And assume only DocD has changed.
                 * The method will be called recursively for DocB, then DocC.
                 * When that happens, it needs to realize that we're already
                 * checking to see if DocB has changed. And so, we should not
                 * recursively check DocB again. It's a no-op: we don't know
                 * if DocB has changed yet or not. So, we skip, and check DocD.
                 */

                continue;
            }

            // if the parseQueue already knows about this file, just ask it
            if ($parseQueue->isFileKnownToParseQueue($dependency)) {
                if ($parseQueue->doesFileRequireParsing($dependency)) {
                    return true;
                }

                continue;
            }

            // dependency no longer exists? We should re-parse this file
            if (!isset($this->fileInfos[$dependency])) {
                return true;
            }

            // finally, we need to recursively ask if this file needs parsing
            if ($this->doesFileRequireParsing($dependency, $parseQueue, $filesAlreadyBeingChecked)) {
                return true;
            }
        }

        // Meta is fresh and no dependencies need parsing
        return false;
    }

    /**
     * Converts foo/bar.rst to foo/bar (the document filename)
     */
    private function getFilenameFromFile(SplFileInfo $file) : string
    {
        return substr($file->getRelativePathname(), 0, -(strlen($this->fileExtension) + 1));
    }
}
