Events
======

The Doctrine RST parser dispatches several different events internally which enable you
to hook into the core of the parser to add custom functionality.

Event Manager
-------------

You can access the ``Doctrine\Common\EventManager`` instance with the ``Doctrine\RST\Configuration::getEventManager()`` method:

.. code-block:: php

    $eventManager = $configuration->getEventManager();

If you want to set your own event manager, you can do so with the ``setEventManager(EventManager $eventManager)`` method:

.. code-block:: php

    use Doctrine\Common\EventManager;

    $eventManager = new EventManager();

    $configuration->setEventManager($eventManager);

Listeners
---------

Add a new listener with the event manager:

.. code-block:: php

    use App\Listeners\DocumentListener;
    use Doctrine\RST\Event\PostParseDocumentEvent;

    $eventManager->addEventListener(
        [PostParseDocumentEvent::POST_PARSE_DOCUMENT /*, other events, if any*/],
        new DocumentListener()
    );

Now, define your listener in ``App\Listeners\DocumentListener``. The ``postParseDocument()``
method will be invoked every time a document is parsed:

.. code-block:: php

    namespace App\Listeners;

    use Doctrine\RST\Event\PostParseDocumentEvent;

    class DocumentListener
    {
        public function postParseDocument(PostParseDocumentEvent $event)
        {
            $documentNode = $event->getDocumentNode();

            // do something with $documentNode
        }
        
        // Other event handlers, if any
        // ...
    }

Available Events
----------------

The events you can listen for and their respective handlers:

.. list-table:: 
   :widths: 2 1 4
   :header-rows: 1
   :stub-columns: 1

   *  - Event name 
      - Invoked handler method
      - Timing of event
   *  - ``PreBuildScanEvent::PRE_BUILD_SCAN`` 
      - ``preBuildScan()`` 
      - before files are scanned, when using the builder.
   *  - ``PreBuildParseEvent::PRE_BUILD_PARSE``
      - ``preBuildParse()``
      - after files are scanned and before they are parsed, when using the builder
   *  - ``PreBuildRenderEvent::PRE_BUILD_RENDER``
      - ``preBuildRender()``
      - after files are parsed and before they are rendered, when using the builder
   *  - ``PostBuildRenderEvent::POST_BUILD_RENDER``
      - ``postBuildRender()`` 
      - after files are rendered, when using the builder
   *  - ``PostNodeCreateEvent::POST_NODE_CREATE``
      - ``postNodeCreate()`` 
      - after a node is created
   *  - ``PreParseDocumentEvent::PRE_PARSE_DOCUMENT``
      - ``preParseDocument()``
      - before a node is parsed
   *  - ``PostParseDocumentEvent::POST_PARSE_DOCUMENT``
      - ``postParseDocument()`` 
      - after a node is parsed
   *  - ``PreNodeRenderEvent::PRE_NODE_RENDER``
      - ``preNodeRender()``
      - before a node is rendered
   *  - ``PostNodeRenderEvent::POST_NODE_RENDER``
      - ``postNodeRender()``
      - after a node is rendered
