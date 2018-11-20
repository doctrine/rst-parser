Introduction
============

The Doctrine RST Parser is a PHP library that can parse `reStructuredText <https://en.wikipedia.org/wiki/ReStructuredText>`_
documents and render them in HTML or LaTeX.

Installation
------------

You can install the Doctrine RST Parser with composer:

.. code-block:: console

    $ composer install doctrine/rst-parser

Basic Usage
-----------

Here is an example script that demonstrates how to use the RST Parser. Create a file named ``rst-test.php``
in the root of your project and paste the following code:

.. code-block:: php

    require 'vendor/autoload.php';

    use Doctrine\RST\Parser;

    $parser = new Parser();

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

Now execute the script:

.. code-block:: console

    $ php rst-test.php

The above would output the following HTML:

.. code-block:: html

    <div class="section" id="hello-world">
    <h1>Hello world</h1>
    </div>
    <div class="section" id="what-is-it">
    <h2>What is it?</h2>
    <p>This is a <strong>RST</strong> document!</p>
    </div>
    <div class="section" id="where-can-i-get-it">
    <h2>Where can I get it?</h2>
    <p>You can get it on the <a href="https://github.com/doctrine/rst-parser">GitHub page</a></p>
    </div>

If you want to render a full HTML document you can do so with the ``renderDocument()`` method:

.. code-block:: php

    echo $document->renderDocument();

The above would output the following:

.. code-block:: html

    <!DOCTYPE html>
    <html>
        <head>
            <meta charset="utf-8" />


        </head>

        <body>
                <div class="section" id="hello-world">
    <h1>Hello world</h1>
    </div>
    <div class="section" id="what-is-it">
    <h2>What is it?</h2>
    <p>This is a <strong>RST</strong> document!</p>
    </div>
    <div class="section" id="where-can-i-get-it">
    <h2>Where can I get it?</h2>
    <p>You can get it on the <a href="https://github.com/doctrine/rst-parser">GitHub page</a></p>
    </div>

        </body>
    </html>

If you would like to customize the rendered HTML take a look at the :ref:`Customizing Rendering <customizing-rendering>` chapter.
