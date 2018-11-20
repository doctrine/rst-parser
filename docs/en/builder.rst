Builder
=======

The ``Doctrine\RST\Builder`` class will parse a whole tree of documents
and generate an output directory containing formatted files.

It can be used like this:

.. code-block:: php

    use Doctrine\RST\Builder;

    $builder = new Builder();
    $builder->build('/path/to/source', '/path/to/output');

It will parse all the files in the ``/path/to/source`` directory, starting with
``index.rst``, scan for dependencies and will generate target files in the ``/path/to/output``
directory. The Default format is HTML.

Configuration
-------------

If you want to customize the builder you can pass a ``Doctrine\RST\Kernel`` instance
with a ``Doctrine\RST\Configuration`` that allows you to customize the configuration
used by the builder:

.. code-block:: php

    use Doctrine\RST\Builder;
    use Doctrine\RST\Configuration;
    use Doctrine\RST\Kernel;

    $configuration = new Configuration();
    $configuration->setBaseUrl('https://www.google.com');
    $kernel = new Kernel($configuration);

    $builder = new Builder($kernel);

You can read more about what configuration options exist in the :ref:`Configuration <configuration>` chapter.

Custom Index Name
-----------------

If your index file is not ``index.rst`` and it is something like ``introduction.rst``
you can customize that using the ``setIndexName()`` method:

.. code-block:: php

    $builder->setIndexName('introduction');
