Custom Text Roles
=================

An interpreted text role can apply special styles to an inline text.

.. code-block:: rst

    This is some :role:`interpreted text`.

You can define custom text roles in your project if needed:

Text role class
---------------

You can write your own text roles by defining a class that implements
``Doctrine\RST\TextRoles\TextRole``. In most use cases it is more convenient
to extend the ``Doctrine\RST\TextRoles\BaseTextRole``.

See `TextRole.php <https://github.com/doctrine/rst-parser/blob/HEAD/lib/TextRoles/TextRole.php>`_ for more information.

Example Text Role
-----------------

.. code-block:: php

    declare(strict_types=1);

    namespace Doctrine\Tests\RST\TextRoles;

    use Doctrine\RST\Environment;
    use Doctrine\RST\Span\SpanToken;
    use Doctrine\RST\TextRoles\BaseTextRole;

    class ExampleRole extends BaseTextRole
    {
        public function getName(): string
        {
            return 'example';
        }

        public function render(Environment $environment, SpanToken $spanToken): string
        {
            return '<samp>' . $spanToken->get('text') . '</samp>';
        }
    }

Now you can register your text role by registering it in a custom directive
factory in your :file:`Configuration.php`:

.. code-block:: php

    $configuration = new Configuration();

    $configuration->addDirectiveFactory(new CustomDirectiveFactory(
        [],
        [new ExampleRole()]
    ));

    return $configuration;
