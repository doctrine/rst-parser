Customizing Rendering
=====================

You can customize individual templates used during the rendering process by configuring
the ``customTemplateDirs`` option using ``setCustomTemplateDirs()`` or ``addCustomTemplateDir()``
methods:

.. code-block:: php

    use Doctrine\RST\Formats\Format;

    $configuration->setFileExtension(Format::HTML); // default is html
    $configuration->setCustomTemplateDirs([
        '/path/to/custom/templates'
    ]);

The files that you can override can be found `here <https://github.com/doctrine/rst-parser/tree/HEAD/lib/Templates/default>`_.

For example, the file ``default/html/anchor.html.twig`` could be overwritten by creating the same file at
``/path/to/custom/templates/default/html/anchor.html.twig``. All of the other templates will still use
the core templates.

If you wanted to customize the LaTeX output you can do so like this:

.. code-block:: php

    $configuration->setFileExtension(Format::LATEX);

Now you can customize the LaTeX output by overriding files in ``/path/to/custom/templates/default/tex``.
