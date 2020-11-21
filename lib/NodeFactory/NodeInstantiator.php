<?php

declare(strict_types=1);

namespace Doctrine\RST\NodeFactory;

use Doctrine\Common\EventManager;
use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\NodeTypes;
use Doctrine\RST\Renderers\NodeRendererFactory;
use InvalidArgumentException;

use function assert;
use function in_array;
use function is_subclass_of;
use function sprintf;

class NodeInstantiator
{
    /** @var string */
    private $type;

    /** @var string */
    private $className;

    /** @var NodeRendererFactory|null */
    private $nodeRendererFactory;

    /** @var EventManager|null */
    private $eventManager;
    /** @var Environment */
    private $environment;

    public function __construct(
        string $type,
        string $className,
        Environment $environment,
        ?NodeRendererFactory $nodeRendererFactory = null,
        ?EventManager $eventManager = null
    ) {
        if (! in_array($type, NodeTypes::NODES, true)) {
            throw new InvalidArgumentException(
                sprintf('Node type %s is not a valid node type.', $type)
            );
        }

        if (! is_subclass_of($className, Node::class)) {
            throw new InvalidArgumentException(
                sprintf('%s class is not a subclass of %s', $className, Node::class)
            );
        }

        $this->type                = $type;
        $this->className           = $className;
        $this->nodeRendererFactory = $nodeRendererFactory;
        $this->eventManager        = $eventManager;
        $this->environment         = $environment;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param mixed[] $arguments
     */
    public function create(array $arguments): Node
    {
        $node = new $this->className(...$arguments);
        assert($node instanceof Node);

        if ($this->nodeRendererFactory !== null) {
            $node->setNodeRendererFactory($this->nodeRendererFactory);
        }

        if ($this->eventManager !== null) {
            $node->setEventManager($this->eventManager);
        }

        $node->setEnvironment($this->environment);

        return $node;
    }
}
