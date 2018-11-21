Configuration
=============

Base URL
--------

Normally, all URLs are generated relative, but if you want to generate absolute URLs
with a base url, you can use the ``baseUrl`` option:

.. code-block:: php

    $configuration->setBaseUrl('https://www.doctrine-project.org');

Base URL Enabled Callable
-------------------------

In some cases, you may want to control when the base url gets used. For this you can set
a callable that will be invoked when generating URLs. The callable receives a string that
contains the path to the current file being rendered. This means you could make the parser
only use the base url on certain paths:

.. code-block:: php

    // only use the base url on paths that contain the string /use-base-url/
    $configuration->setBaseUrlEnabledCallable(static function(string $path) : bool {
        return strpos($path, '/use-base-url/') !== false;
    });

Abort on Error
--------------

By default if an error is encountered, the parsing will be aborted. You can easily
change this like this:

.. code-block:: php

    $configuration->abortOnError(false);

Ignoring Invalid References
---------------------------

By default, invalid references and links will be reported as errors. If you want to
ignore them you can do so like this:

.. code-block:: php

    $configuration->setIgnoreInvalidReferences(true);

Format
------

By default, the Doctrine RST Parser comes with two formats, HTML and LaTeX. HTML is the default format
and you can configure which format to use by using the following constants:

- ``Doctrine\RST\Formats\Format::HTML``
- ``Doctrine\RST\Formats\Format::LATEX``

And you can configure the format to render like this:

.. code-block:: php

    use Doctrine\RST\Formats\Format;

    $configuration->setFileExtension(Format::LATEX);

You can read more about formats in the :ref:`Formats <formats>` chapter.

Indent HTML
-----------

By default, the outputted HTML is not indented consistently. If you would like the outputted HTML to be
indented consistently, you can enable this feature using the ``setIndentHTML(bool $indentHTML)`` method:

.. code-block:: php

    $configuration->setIndentHTML(true);

.. note::

    This feature only works when rendering full HTML documents using the
    ``Doctrine\RST\Nodes\DocumentNode::renderDocument()`` method. If you render
    an individual node with the ``render()`` method, the outputted HTML will not be indented.
