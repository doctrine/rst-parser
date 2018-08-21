<?php

declare(strict_types=1);

namespace Doctrine\RST;

use function array_pop;
use function array_shift;
use function basename;
use function count;
use function dirname;
use function explode;
use function iconv;
use function implode;
use function in_array;
use function preg_match;
use function preg_replace;
use function strtolower;
use function substr;
use function trim;

class Environment
{
    /** @var string[] */
    public static $letters = ['=', '-', '~', '*', '+', '^', '"'];

    /** @var ErrorManager */
    public $errorManager = null;

    /** @var string */
    public static $tableLetter = '=';

    /** @var string */
    public static $prettyTableLetter = '-';

    /** @var string */
    public static $prettyTableHeader = '=';

    /** @var string */
    public static $prettyTableJoint = '+';

    /** @var int */
    protected $currentTitleLevel = 0;

    /** @var string[] */
    protected $titleLetters = [];

    /** @var string */
    protected $currentFileName = '';

    /** @var string */
    protected $currentDirectory = '.';

    /** @var string */
    protected $targetDirectory = '.';

    /** @var null|string */
    protected $url = null;

    /** @var Reference[] */
    protected $references = [];

    /** @var Metas */
    protected $metas;

    /** @var string[] */
    protected $dependencies = [];

    /** @var string[] */
    protected $variables = [];

    /** @var string[] */
    protected $links = [];

    /** @var int[] */
    protected $levels = [];

    /** @var int[] */
    protected $counters = [];

    /** @var bool */
    protected $relativeUrls = true;

    /** @var string[] */
    protected $anonymous = [];

    public function __construct()
    {
        $this->errorManager = new ErrorManager();

        $this->reset();
    }

    public function reset() : void
    {
        $this->titleLetters      = [];
        $this->currentTitleLevel = 0;
        $this->levels            = [];
        $this->counters          = [];

        for ($level = 0; $level < 16; $level++) {
            $this->levels[$level]   = 1;
            $this->counters[$level] = 0;
        }
    }

    public function getErrorManager() : ErrorManager
    {
        return $this->errorManager;
    }

    public function setErrorManager(ErrorManager $errorManager) : void
    {
        $this->errorManager = $errorManager;
    }

    public function setMetas(Metas $metas) : void
    {
        $this->metas = $metas;
    }

    /**
     * @return null|mixed[][]
     */
    public function getParent() : ?array
    {
        if ($this->currentFileName === '' || $this->metas === null) {
            return null;
        }

        $meta = $this->metas->get($this->currentFileName);

        if ($meta === null || ! isset($meta['parent'])) {
            return null;
        }

        return $this->metas->get($meta['parent']);
    }

    /**
     * @return null|mixed[]
     */
    public function getMyToc() : ?array
    {
        $parent = $this->getParent();

        if ($parent === null) {
            return null;
        }

        foreach ($parent['tocs'] as $toc) {
            if (! in_array($this->currentFileName, $toc, true)) {
                continue;
            }

            $before = [];
            $after  = $toc;

            while ($after) {
                $file = array_shift($after);

                if ($file === $this->currentFileName) {
                    return [$before, $after];
                }

                $before[] = $file;
            }
        }

        return null;
    }

    public function registerReference(Reference $reference) : void
    {
        $this->references[$reference->getName()] = $reference;
    }

    /**
     * @return null|mixed[]
     */
    public function resolve(string $section, string $data) : ?array
    {
        if (isset($this->references[$section])) {
            $reference = $this->references[$section];

            return $reference->resolve($this, $data);
        }

        $this->errorManager->error('Unknown reference section ' . $section);

        return null;
    }

    /**
     * @return null|string[]
     */
    public function resolveByText(string $section, string $text) : ?array
    {
        if (isset($this->references[$section])) {
            $reference = $this->references[$section];

            return $reference->resolveByText($this, $text);
        }

        $this->errorManager->error('Unknown reference section ' . $section);

        return null;
    }

    /**
     * @return null|string[]
     */
    public function found(string $section, string $data) : ?array
    {
        if (isset($this->references[$section])) {
            $reference = $this->references[$section];

            $reference->found($this, $data);

            return null;
        }

        $this->errorManager->error('Unknown reference section ' . $section);

        return null;
    }

    /**
     * @param mixed $value
     */
    public function setVariable(string $variable, $value) : void
    {
        $this->variables[$variable] = $value;
    }

    public function createTitle(int $level) : string
    {
        for ($currentLevel = 0; $currentLevel < 16; $currentLevel++) {
            if ($currentLevel <= $level) {
                continue;
            }

            $this->levels[$currentLevel]   = 1;
            $this->counters[$currentLevel] = 0;
        }

        $this->levels[$level] = 1;
        $this->counters[$level]++;
        $token = ['title'];

        for ($i = 1; $i <= $level; $i++) {
            $token[] = $this->counters[$i];
        }

        return implode('.', $token);
    }

    public function getNumber(int $level) : int
    {
        return $this->levels[$level]++;
    }

    /**
     * @param null|mixed $default
     *
     * @return mixed
     */
    public function getVariable(string $variable, $default = null)
    {
        if (isset($this->variables[$variable])) {
            return $this->variables[$variable];
        }

        return $default;
    }

    public function setLink(string $name, string $url) : void
    {
        $name = trim(strtolower($name));

        if ($name === '_') {
            $name = array_shift($this->anonymous);
        }

        $this->links[$name] = trim($url);
    }

    public function resetAnonymousStack() : void
    {
        $this->anonymous = [];
    }

    public function pushAnonymous(string $name) : void
    {
        $this->anonymous[] = trim(strtolower($name));
    }

    public function getLink(string $name, bool $relative = true) : ?string
    {
        $name = trim(strtolower($name));

        if (isset($this->links[$name])) {
            $link = $this->links[$name];

            if ($relative) {
                return $this->relativeUrl($link);
            }

            return $link;
        }

        return null;
    }

    public function addDependency(string $dependency) : void
    {
        $dependency           = $this->canonicalUrl($dependency);
        $this->dependencies[] = $dependency;
    }

    /**
     * @return string[]
     */
    public function getDependencies() : array
    {
        return $this->dependencies;
    }

    /**
     * Resolves a relative URL using directories, for instance, if the
     * current directory is "path/to/something", and you want to get the
     * relative URL to "path/to/something/else.html", the result will
     * be else.html. Else, "../" will be added to go to the upper directory
     */
    public function relativeUrl(?string $url) : ?string
    {
        if ($url === null) {
            return null;
        }

        // If string contains ://, it is considered as absolute
        if (preg_match('/:\\/\\//mUsi', $url) > 0) {
            return $url;
        }

        // If string begins with "/", the "/" is removed to resolve the
        // relative path
        if ($url !== '' && $url[0] === '/') {
            $url = substr($url, 1);

            if ($this->samePrefix($url)) {
                // If the prefix is the same, simply returns the file name
                $relative = basename($url);
            } else {
                // Else, returns enough ../ to get upper
                $relative = '';

                for ($k=0; $k<$this->getDepth(); $k++) {
                    $relative .= '../';
                }

                $relative .= $url;
            }
        } else {
            $relative = $url;
        }

        return $relative;
    }

    public function useRelativeUrls() : bool
    {
        return $this->relativeUrls;
    }

    public function setUseRelativeUrls(bool $relativeUrls) : void
    {
        $this->relativeUrls = $relativeUrls;
    }

    public function getDepth() : int
    {
        return count(explode('/', $this->currentFileName))-1;
    }

    protected function samePrefix(string $url) : bool
    {
        $partsA = explode('/', $url);
        $partsB = explode('/', $this->currentFileName);

        $n = count($partsA);

        if ($n !== count($partsB)) {
            return false;
        }

        unset($partsA[$n - 1]);
        unset($partsB[$n - 1]);

        return $partsA === $partsB;
    }

    public function getDirName() : string
    {
        $dirname = dirname($this->currentFileName);

        if ($dirname === '.') {
            return '';
        }

        return $dirname;
    }

    protected function canonicalize(string $url) : string
    {
        $parts = explode('/', $url);
        $stack = [];

        foreach ($parts as $part) {
            if ($part === '..') {
                array_pop($stack);
            } else {
                $stack[] = $part;
            }
        }

        return implode('/', $stack);
    }

    public function canonicalUrl(string $url) : ?string
    {
        if ($url !== '') {
            if ($url[0] === '/') {
                // If the URL begins with a "/", the following is the
                // canonical URL
                return substr($url, 1);
            }

            // Else, the canonical name is under the current dir
            if ($this->getDirName() !== '') {
                return $this->canonicalize($this->getDirName() . '/' . $url);
            }

            return $this->canonicalize($url);
        }

        return null;
    }

    public function setCurrentFileName(string $filename) : void
    {
        $this->currentFileName = $filename;
    }

    public function setCurrentDirectory(string $directory) : void
    {
        $this->currentDirectory = $directory;
    }

    public function absoluteRelativePath(string $url) : string
    {
        return $this->currentDirectory . '/' . $this->getDirName() . '/' . $this->relativeUrl($url);
    }

    public function setTargetDirectory(string $directory) : void
    {
        $this->targetDirectory = $directory;
    }

    public function getTargetDirectory() : string
    {
        return $this->targetDirectory;
    }

    public function getUrl() : string
    {
        if ($this->url !== null) {
            return $this->url;
        }

        return $this->currentFileName;
    }

    public function setUrl(string $url) : void
    {
        if ($this->getDirName() !== '') {
            $url = $this->getDirName() . '/' . $url;
        }

        $this->url = $url;
    }

    public function getMetas() : ?Metas
    {
        return $this->metas;
    }

    public function getLevel(string $letter) : int
    {
        foreach ($this->titleLetters as $level => $titleLetter) {
            if ($letter === $titleLetter) {
                return $level;
            }
        }

        $this->currentTitleLevel++;
        $this->titleLetters[$this->currentTitleLevel] = $letter;

        return $this->currentTitleLevel;
    }

    /**
     * @return string[]
     */
    public function getTitleLetters() : array
    {
        return $this->titleLetters;
    }

    public static function slugify(string $text) : string
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = (string) iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        return $text;
    }
}
