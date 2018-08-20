<?php

namespace Gregwar\RST\References;

use Gregwar\RST\Reference;
use Gregwar\RST\Environment;

class Doc extends Reference
{
    protected $name;

    public function __construct($name = 'doc')
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function resolve(Environment $environment, $data)
    {
        $metas = $environment->getMetas();
        $file = $environment->canonicalUrl($data);

        if ($metas) {
            $entry = $metas->get($file);

            // only call relativeUrl() if a document was found
            // so we can later try to link to an anchor in this document
            if ($entry['url']) {
                $entry['url'] = $environment->relativeUrl('/'.$entry['url']);
            }
        } else {
            $entry = array(
                'title' => '(unresolved)',
                'url' => '#'
            );
        }

        return $entry;
    }

    public function resolveByText(Environment $environment, $text)
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
                $entry['url'] = $environment->relativeUrl('/'.$entry['url']);
            }
        } else {
            $entry = array(
                'title' => '(unresolved)',
                'url' => '#'
            );
        }

        return $entry;
    }

    public function found(Environment $environment, $data)
    {
        $environment->addDependency($data);
    }

    private function findEntryByText(array $titles, $text)
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
