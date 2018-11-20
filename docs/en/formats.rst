Formats
=======

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
