<?php

declare(strict_types=1);

namespace Gregwar\RST\References;

use Gregwar\RST\Environment;
use Gregwar\RST\Reference;
use function trim;

class Doc extends Reference
{
    protected $name;

    public function __construct($name = 'doc')
    {
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function resolve(Environment $environment, $data) : ?array
    {
        $metas = $environment->getMetas();
        $file  = $environment->canonicalUrl($data);

        if ($metas) {
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

    public function resolveByText(Environment $environment, $text) : array
    {
        $text = trim($text);

        $metas = $environment->getMetas();

        $entry = null;

        if ($metas) {
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

    public function found(Environment $environment, $data) : void
    {
        $environment->addDependency($data);
    }

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
    }
}
