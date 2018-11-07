Introduction
============

PHP library to parse `reStructuredText <https://en.wikipedia.org/wiki/ReStructuredText>`_
documents. This library is used to generate documentation for the `Doctrine <https://www.doctrine-project.org>`_
project `website <https://github.com/doctrine/doctrine-website>`_.

Installation
============

You can install the Doctrine RST Parser with composer:

.. code-block:: console

    $ composer install doctrine/rst-parser

Usage
=====

.. code-block:: php

    <?php

    require 'vendor/autoload.php';

    use Doctrine\RST\Configuration;
    use Doctrine\RST\Parser;
    use Doctrine\RST\Kernel;

    $configuration = new Configuration();
    $kernel = new Kernel($configuration);

    $parser = new Parser($kernel);

    // RST document
    $rst = '
    Hello world
    ===========

    What is it?
    ----------
    This is a **RST** document!

    Where can I get it?
    -------------------
    You can get it on the `GitHub page <https://github.com/doctrine/rst-parser>`_
    ';

    // Parse it
    $document = $parser->parse($rst);

    // Render it
    echo $document->render();

    /* Will output, in HTML mode:
    <a id="hello-world"></a><h1>Hello world</h1>
    <a id="what-is-it"></a><h2>What is it?</h2>
    <p>This is a <strong>RST</strong> document!</p>
    <a id="where-can-i-get-it"></a><h2>Where can I get it?</h2>
    <p>You can get it on the <a href="https://github.com/doctrine/rst-parser">GitHub page</a></p>
    */

Using the builder
=================

The builder is another tool that will parses a whole tree of documents
and generates an output directory containing files.

You can simply use it with:

.. code-block:: php

    <?php

    require 'vendor/autoload.php';

    use Doctrine\RST\Builder;
    use Doctrine\RST\Configuration;
    use Doctrine\RST\Kernel;

    $configuration = new Configuration();
    $kernel = new Kernel($configuration);

    $builder = new Builder($kernel);
    $builder->build('input', 'output');

It will parse all the files in the ``input`` directory, starting with
``index.rst`` and scanning for dependencies references and generates you
target files in the ``output`` directory. Default format is HTML.

You can use those methods on it to customize the build:

-  ``copy($source, $destination)``: copy the ``$source`` file or
   directory to the ``$destination`` file or directory of the build
-  ``mkdir($directory)``: create the ``$directory`` in build directory
-  ``addHook($function)``: adds an hook that will be called after each
   document is parsed, this hook will be called with the ``$document``
   as parameter and can then tweak it as you want
-  ``addBeforeHook($function)``: adds an hook that will be called before
   parsing the document, the parser will be passed as a parameter

Configuration
=============

Base URL
--------

Normally, all URLs are generated relative, but if you want to generate absolute URLs
with a base url, you can use the ``baseUrl`` option:

.. code-block:: php

    <?php

    $configuration->setBaseUrl('https://www.doctrine-project.org');

Base URL Enabled Callable
-------------------------

In some cases, you may want to control when the base url gets used. For this you can set
a callable that will be invoked when generating URLs. The callable receives a string that
contains the path to the current file being rendered. This means you could make the parser
only use the base url on certain paths:

.. code-block:: php

    <?php

    // only use the base url on paths that contain the string /use-base-url/
    $configuration->setBaseUrlEnabledCallable(static function(string $path) : bool {
        return strpos($path, '/use-base-url/') !== false;
    });

Custom Directives
=================

Step 1: Extends the Directive class
-----------------------------------

Write your own class that extends the ``Doctrine\RST\Directive`` class,
and define the method ``getName()`` that return the directive name.

You can then redefine one of the following method:

-  ``processAction()`` if your directive simply tweak the document
   without modifying the nodes
-  ``processNode()`` if your directive is adding a node
-  ``process()`` if your directive is tweaking the node that just
   follows it

See ``Directive.php`` for more information

Step 2: Register your directive
-------------------------------

You can register your directive by directly calling
``registerDirective()`` on your ``Parser`` object.

.. code-block:: php

    <?php

    use App\RST\Directive\CautionDirective;

    $parser->registerDirective(new CautionDirective());

Or you can pass an array of directives when constructing your Kernel:

.. code-block:: php

    <?php

    use App\RST\Directive\CautionDirective;

    $kernel = new Kernel($configuration, [
        new CautionDirective()
    ]);

    $builder = new Builder($kernel);

The ``CautionDirective`` class would look like this:

.. code-block:: php

    <?php

    declare(strict_types=1);

    namespace App\RST\Directive;

    use Doctrine\RST\Nodes\Node;
    use Doctrine\RST\Nodes\WrapperNode;
    use Doctrine\RST\Parser;
    use Doctrine\RST\SubDirective;

    class CautionDirective extends SubDirective
    {
        public function getName() : string
        {
            return 'caution';
        }

        /**
         * @param string[] $options
         */
        public function processSub(
            Parser $parser,
            ?Node $document,
            string $variable,
            string $data,
            array $options
        ) : ?Node {
            return $parser->getNodeFactory()->createWrapper($document, '<div class="caution">', '</div>');
        }
    }

Now you can use the directive like this:

.. code-block::

    .. caution::

        Be careful when using this functionality!

Which would output the following HTML:

.. code-block:: html

    <div class="caution"><p>Be careful when using this functionality!</p></div>

Attribution
===========

This repository was forked from `Gregwar <https://github.com/Gregwar/RST>`_ for the `Doctrine
Website <https://github.com/doctrine/doctrine-website>`_.

License
=======

This library is under MIT license.
