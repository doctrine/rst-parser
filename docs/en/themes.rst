Themes
======

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

Even with a theme, the rendering engine will continue to
use a ``default`` directory (e.g. ``/path/to/custom/templates/default/html``
as a fallback for any templates (see :doc:`/customizing-rendering`).
