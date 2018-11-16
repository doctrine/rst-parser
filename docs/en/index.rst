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

Customizing Rendering
---------------------

You can customize individual templates used during the rendering process by configuring
the ``customTemplateDirs`` option using ``setCustomTemplateDirs()`` or ``addCustomTemplateDir()``:

.. code-block:: php

    use Doctrine\RST\Formats\Format;

    $configuration->setFileExtension(Format::HTML); // default is html
    $configuration->setCustomTemplateDirs([
        '/path/to/custom/templates'
    ]);

The files that you can override can be found `here <https://github.com/doctrine/rst-parser/tree/master/lib/Templates>`_. For example, the file ``default/html/anchor.html.twig`` could be overwritten by creating the same file at
``/path/to/custom/templates/default/html/anchor.html.twig``. All of the other templates will still use
the core templates.

If you wanted to customize the LaTeX output you can do so like this:

.. code-block:: php

    $configuration->setFileExtension(Format::LATEX);

Now you can customize the LaTeX output by overriding files in ``/path/to/custom/templates/default/tex``.

Themes
------

Similar to customizing individual parts of the rendering, you can have different themes that can be shared.

.. code-block:: php

    use Doctrine\RST\Formats\Format;

    $configuration->setFileExtension(Format::HTML);
    $configuration->setCustomTemplateDirs([
        '/path/to/custom/templates'
    ]);
    $configuration->setTheme('my_theme');

Now create a new directory for your theme at ``/path/to/custom/templates/my_theme/html``. Create a file
named ``layout.html.twig`` and you can customize the layout that wraps all generated html files.

.. code-block:: twig

    <!DOCTYPE html>
    <html>
        <head>
            <meta charset="utf-8" />

            {% block head '' %}
        </head>

        <body>
            {% block body '' %}
        </body>
    </html>

Formats
-------

In addition to templates and themes, you can build formats which allow you to completely implement your
own rendering. This library comes with two formats by default, HTML and LaTeX.

To build your own format you need to implement the ``Doctrine\RST\Formats\Format`` interface:

.. code-block:: php

    namespace App\RST\MySpecial;

    use App\MySpecial\MySpecialGenerator;
    use Doctrine\RST\Directives\Directive;
    use Doctrine\RST\Formats\Format;
    use Doctrine\RST\Nodes;
    use Doctrine\RST\Renderers\CallableNodeRendererFactory;
    use Doctrine\RST\Renderers\NodeRendererFactory;

    class MySpecialFormat implements Format
    {
        /** @var MySpecialGenerator */
        private $mySpecialGenerator;

        public function __construct(MySpecialGenerator $mySpecialGenerator)
        {
            $this->mySpecialGenerator = $mySpecialGenerator;
        }

        public function getFileExtension() : string
        {
            return 'myspecial';
        }

        /**
         * @return Directive[]
         */
        public function getDirectives() : array
        {
            return [
                // ...
            ];
        }

        /**
         * @return NodeRendererFactory[]
         */
        public function getNodeRendererFactories() : array
        {
            return [
                Nodes\AnchorNode::class => new CallableNodeRendererFactory(
                    function (Nodes\AnchorNode $node) {
                        return new MySpecial\Renderers\AnchorNodeRenderer(
                            $node,
                            $this->mySpecialGenerator
                        );
                    }
                ),

                // implement the NodeRendererFactory interface for every node type
            ];
        }
    }

The ``App\RST\MySpecial\Renderers\AnchorNodeRenderer`` would look like this:

.. code-block:: php

    namespace App\RST\MySpecial\Renderers;

    use App\MySpecial\MySpecialGenerator;
    use Doctrine\RST\Nodes\AnchorNode;
    use Doctrine\RST\Renderers\NodeRenderer;

    class AnchorNodeRenderer implements NodeRenderer
    {
        /** @var AnchorNode */
        private $anchorNode;

        /** @var MySpecialGenerator */
        private $mySpecialGenerator;

        public function __construct(AnchorNode $anchorNode, MySpecialGenerator $mySpecialGenerator)
        {
            $this->anchorNode         = $anchorNode;
            $this->mySpecialGenerator = $mySpecialGenerator;
        }

        public function render() : string
        {
            // render the node using the MySpecialGenerator instance
        }
    }

Now add the format to the ``Configuration``:

.. code-block:: php

    use App\MySpecial\MySpecialGenerator;
    use App\RST\MySpecial\MySpecialFormat;

    $configuration->addFormat(new MySpecialFormat(new MySpecialGenerator()));

Use the format:

.. code-block:: php

    $configuration->setFileExtension('myspecial');

Events
======

The Doctrine RST parser dispatches several different events internally which enable you
to hook in to the core of the parser to add custom functionality.

Event Manager
-------------

You can access the ``Doctrine\Common\EventManager`` instance with the ``getEventManager()`` method:

.. code-block:: php

    $eventManager = $configuration->getEventManager();

If you want to set your own you can do so with the ``setEventManager(EventManager $eventManager)`` method:

.. code-block:: php

    use Doctrine\Common\EventManager;

    $eventManager = new EventManager();

    $configuration->setEventManager($eventManager);

Listeners
---------

Add a new listener with the event manager:

.. code-block:: php

    use App\Listeners\PostParseDocumentListener;
    use Doctrine\RST\Event\PostParseDocumentEvent;

    $eventManager->addEventListener(
        [PostParseDocumentEvent::POST_PARSE_DOCUMENT],
        new PostParseDocumentListener()
    );

Now define your listener in ``App\Listeners\PostParseDocumentListener``. The ``postParseDocument()``
method will be notified every time a document is parsed:

.. code-block:: php

    namespace App\Listeners;

    use Doctrine\RST\Event\PostParseDocumentEvent;
    use Doctrine\RST\Event\PostParseDocumentEvent;

    class PostParseDocumentListener
    {
        public function postParseDocument(PostParseDocumentEvent $event)
        {
            $documentNode = $event->getDocumentNode();

            // do something with $documentNode
        }
    }

The events you can listen for are as follows:

- ``PreBuildScanEvent::PRE_BUILD_SCAN`` - Dispatches a method named ``preBuildScan()`` before files are scanned when using the builder.
- ``PreBuildParseEvent::PRE_BUILD_PARSE`` - Dispatches a method named ``preBuildParse()`` before files are parsed and after they are scanned when using the builder.
- ``PreBuildRenderEvent::PRE_BUILD_RENDER`` - Dispatches a method named ``preBuildRender()`` before files are rendered and after they are parsed when using the builder.
- ``PostBuildRenderEvent::POST_BUILD_RENDER`` - Dispatches a method named ``postBuildRender()`` after files are rendered when using the builder.
- ``PostNodeCreateEvent::POST_NODE_CREATE`` - Dispatches a method named ``postNodeCreate()`` after a node is created.
- ``PreParseDocumentEvent::PRE_PARSE_DOCUMENT`` - Dispatches a method named ``preParseDocument()`` before a node is parsed.
- ``PostParseDocumentEvent::POST_PARSE_DOCUMENT`` - Dispatches a method named ``postParseDocument()`` after a node is parsed.
- ``PreNodeRenderEvent::PRE_NODE_RENDER`` - Dispatches a method named ``preNodeRender()`` before a node is rendered.
- ``PostNodeRenderEvent::POST_NODE_RENDER`` - Dispatches a method named ``postNodeRender()`` after a node is rendered.

Custom Directives
=================

Step 1: Extends the Directive class
-----------------------------------

Write your own class that extends the ``Doctrine\RST\Directives\Directive`` class,
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

    use App\RST\Directive\CautionDirective;

    $parser->registerDirective(new CautionDirective());

Or you can pass an array of directives when constructing your Kernel:

.. code-block:: php

    use App\RST\Directive\CautionDirective;

    $kernel = new Kernel($configuration, [
        new CautionDirective()
    ]);

    $builder = new Builder($kernel);

The ``CautionDirective`` class would look like this:

.. code-block:: php

    declare(strict_types=1);

    namespace App\RST\Directive;

    use Doctrine\RST\Nodes\Node;
    use Doctrine\RST\Nodes\WrapperNode;
    use Doctrine\RST\Parser;
    use Doctrine\RST\Directives\SubDirective;

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
            $divOpen = $parser->renderTemplate('div-open.html.twig', [
                'class' => 'caution',
            ]);

            return $parser->getNodeFactory()->createWrapper($document, $divOpen, '</div>');
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
