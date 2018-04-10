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
        $metas = $environment->getMetas();

        $entry = null;

        if ($metas) {
            // try to lookup the document reference by title
            foreach ($metas->getAll() as $e) {
                if (trim($e['title']) === trim($text)) {
                    $entry = $e;
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
}
