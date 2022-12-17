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

Available Events
----------------

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
- ``PreReferenceResolvedEvent::PRE_REFERENCED_RESOVED`` - Dispatches a method named
  ``preReferenceResolved()`` before a reference is resolved. If the ``$resolvedReference``
  of the event is set to any non null value, resolving of references is stopped
  and the ``$resolvedReference`` of the event is used instead.
