Custom Directives
=================

Directives are designed to be an extension mechanism for reStructuredText. It enables you to add custom
functionality to reStructuredText without having to add new custom syntaxes.

Directive Class
---------------

You can write your own directives by defining a class that extends the ``Doctrine\RST\Directives\Directive``
class and defines the method ``getName()`` that returns the directive name.

You can then implement one of the following methods:

-  ``processAction()`` if your directive simply tweak the document
   without modifying the nodes
-  ``processNode()`` if your directive is adding a node
-  ``process()`` if your directive is tweaking the node that just
   follows it

See `Directive.php <https://github.com/doctrine/rst-parser/blob/master/lib/Directives/Directive.php>`_ for more information.

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

Now you can register your directive by passing it to the 2nd argument of the ``Doctrine\RST\Kernel`` class:

.. code-block:: php

    use App\RST\Directives\ExampleDirective;

    $kernel = new Kernel($configuration, [
        new ExampleDirective()
    ]);

    $builder = new Builder($kernel);

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
