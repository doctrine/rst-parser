Custom Directives
=================

Directives are designed to be an extension mechanism for reStructuredText.
It enables you to add custom functionality to reStructuredText without having
to add new custom syntaxes.

Predefined Directives
---------------------

The following directives are predefined and can be used to define a directive
with a custom name and template. For more sophisticated directives see section
:ref:`custom_directive_implementation`.

Ignored Directives
~~~~~~~~~~~~~~~~~~

In many projects directives should be ignored for some reason. These directives
are often still in the rst files for historic reasons but play no role anymore.

A common example is the ``.. index::`` directive when no index generation is
provided.

You can register ignored directives as follows:

You can register ignored directives by passing objects of the type
``Doctrine\RST\Directives\Ignored`` to the array in the 2nd argument of the
``Doctrine\RST\Kernel`` class:

.. code-block:: php

    use App\RST\Directives\ExampleDirective;

    $kernel = new Kernel($configuration, [
        new Directives\Ignored('todo'),
        new Directives\Ignored('index'),
        new Directives\Ignored('ignore-me'),
    ]);

    $builder = new Builder($kernel);

The following directives with all content, data and attributes will now be
ignored and cause neither error nor warning nor output:

.. code-block:: rst

    .. todo:: This is a TODO directive, ignore me!

    .. index::
        One Index
        Another Index

    .. ignore-me:: Ignore this
        :Argument 1: Ignore me
        :Argument 2: Ignore me

        And ignore this content as well.


Data Directives
~~~~~~~~~~~~~~~

Data directives pass the data (the part after the double double points)
to a template to be processed. The template must take care of escaping the
content as is standard in twig.

You can register data directives by passing objects of the type
``Doctrine\RST\Directives\Data`` to the array in the 2nd argument of the
``Doctrine\RST\Kernel`` class:

.. code-block:: php

    use App\RST\Directives\ExampleDirective;

    $kernel = new Kernel($configuration, [
        new Directives\Data('my-data-directive'),  // uses template directives/my-data-directive.html.twig
        new Directives\Data('some-directive', 'directives/mytemplate.html.twig'),
    ]);

    $builder = new Builder($kernel);

The following directives pass the *data* to the template. If there are arguments
they are ignored. If there is content it is treated as normal node.

.. code-block:: rst

    .. my-data-directive:: Test

    .. my-data-directive:: The data
        :Argument 1: The argument is ignored

    .. my-data-directive:: The content is just a normal node

        Some content, treated as normal node.

Wrapper Directives
~~~~~~~~~~~~~~~~~~

Wrapper directives pass the data to a template. The template must contain 3 pipes
``|||`` once. All content (all indented rst code until the next non-indented
is found) will be parsed, all standard directives, text-roles etc applied.
Arguments will be ignored.

It will then be wrapped by the result of the template, split by ``|||``.

You can register wrapper directives by passing objects of the type
``Doctrine\RST\Directives\Wrapper`` to the array in the 2nd argument of the
``Doctrine\RST\Kernel`` class:

.. code-block:: php

    use App\RST\Directives\ExampleDirective;

    $kernel = new Kernel($configuration, [
        new Directives\Wrapper('versionadded'), // uses template directives/versionadded.html.twig
        new Directives\Wrapper('another-wrapper', 'directives/some-wrapper-template.html.twig'),
    ]);

    $builder = new Builder($kernel);

The template could look like this:

.. code-block:: html

    <div class="card card-versionadded">
        <div class="card-header">
            New in version {{ data }}
        </div>
        |||
    </div>

And can be used like this:

.. code-block:: rst

    .. versionadded:: 10.2
        Starting with TYPO3 10.2 hooks and signals have been replaced by a
        `PSR-14 <https://www.php-fig.org/psr/psr-14/>`__ based
        **event** dispatching system.


.. _custom_directive_implementation:

Writing a Custom Directive Implementation
-----------------------------------------

You can write your own directives by defining a class that extends the ``Doctrine\RST\Directives\Directive``
class and defines the method ``getName()`` that returns the directive name.

You can then implement one of the following methods:

-  ``processAction()`` if your directive simply tweak the document
   without modifying the nodes
-  ``processNode()`` if your directive is adding a node
-  ``process()`` if your directive is tweaking the node that just
   follows it

See `Directive.php <https://github.com/doctrine/rst-parser/blob/HEAD/lib/Directives/Directive.php>`_ for more information.

Example Directive
-----------------

.. code-block:: php

    namespace App\RST\Directives;

    use Doctrine\RST\Nodes\Node;
    use Doctrine\RST\Parser;
    use Doctrine\RST\Directives\Directive;

    class ExampleDirective extends Directive
    {
        public function getName() : string
        {
            return 'example';
        }

        /**
         * @param string[] $options
         */
        public function process(
            Parser $parser,
            ?Node $node,
            string $variable,
            string $data,
            array $options
        ) : void {
            // do something to $node
        }
    }

Now you can register your directive by registering it in a custom directive
factory in your :file:`Configuration.php`:

.. code-block:: php

    $configuration = new Configuration();

    $configuration->addDirectiveFactory(new CustomDirectiveFactory(
        [new ExampleDirective()]
    ));

    return $configuration;


SubDirective Class
------------------

You can also extend the ``Doctrine\RST\Directives\SubDirective`` class and implement the ``processSub()`` method if
you want the sub block to be parsed. Here is an example ``CautionDirective``:

.. code-block:: php

    namespace App\RST\Directives;

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

            return $parser->getNodeFactory()->createWrapperNode($document, $divOpen, '</div>');
        }
    }

Now you can use the directive like this and it can contain other reStructuredText syntaxes:

.. code-block::

    .. caution::

        This is some **bold** text!

The above example would output the following HTML:

.. code-block:: html

    <div class="caution"><p>This is some <strong>bold</strong> text!</p></div>
