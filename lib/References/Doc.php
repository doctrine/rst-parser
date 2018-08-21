<?php

declare(strict_types=1);

namespace Doctrine\RST\References;

use Doctrine\RST\Environment;
use Doctrine\RST\Reference;
use function trim;

class Doc extends Reference
{
    /** @var string */
    protected $name;

    public function __construct(string $name = 'doc')
    {
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function resolve(Environment $environment, string $data) : ?array
    {
        $metas = $environment->getMetas();
        $file  = $environment->canonicalUrl($data);

        if ($metas !== null && $file !== null) {
            $entry = $metas->get($file);

            // only call relativeUrl() if a document was found
            // so we can later try to link to an anchor in this document
            if ($entry['url']) {
                $entry['url'] = $environment->relativeUrl('/' . $entry['url']);
            }
        } else {
            $entry = [
                'title' => '(unresolved)',
                'url' => '#',
            ];
        }

        return $entry;
    }

    /**
     * @return string[]
     */
    public function resolveByText(Environment $environment, string $text) : array
    {
        $text = trim($text);

        $metas = $environment->getMetas();

        $entry = null;

        if ($metas !== null) {
            // try to lookup the document reference by title
            foreach ($metas->getAll() as $e) {
                if (trim($e['title']) === $text) {
                    $entry = $e;
                    break;
                }

                // recursively search all the children nodes for a match
                foreach ($e['titles'] as $title) {
                    if ($this->findEntryByText($title[1], $text)) {
                        $entry = $e;
                        break;
                    }
                }
            }

            // only call relativeUrl() if a document was found
            // so we can later try to link to an anchor in this document
            if ($entry['url'] !== '') {
                $entry['url'] = $environment->relativeUrl('/' . $entry['url']);
            }
        } else {
            $entry = [
                'title' => '(unresolved)',
                'url' => '#',
            ];
        }

        return $entry;
    }

    public function found(Environment $environment, string $data) : void
    {
        $environment->addDependency($data);
    }

    /**
     * @param mixed[] $titles
     */
    private function findEntryByText(array $titles, string $text) : bool
    {
        foreach ($titles as $title) {
            if ($title[0] === $text) {
                return true;
            }

            if ($this->findEntryByText($title[1], $text)) {
                return true;
            }
        }

        return false;
    }
}
