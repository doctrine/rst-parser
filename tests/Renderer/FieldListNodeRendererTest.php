<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\Renderer;

use Doctrine\RST\Configuration;
use Doctrine\RST\Environment;
use Doctrine\RST\HTML\Renderers\FieldListNodeRenderer;
use Doctrine\RST\Nodes\FieldListNode;
use Doctrine\RST\Parser\FieldOption;
use PHPUnit\Framework\TestCase;

final class FieldListNodeRendererTest extends TestCase
{
    private FieldListNode $listNode;
    private FieldListNodeRenderer $fieldListNodeRenderer;

    protected function setUp(): void
    {
        $environment                 = new Environment(new Configuration());
        $this->listNode              = new FieldListNode([
            new FieldOption('what', 0, 'that'),
            new FieldOption('how', 0, 'like this'),
        ]);
        $this->fieldListNodeRenderer = new FieldListNodeRenderer($this->listNode, $environment->getTemplateRenderer());
    }

    public function testFieldListRenders(): void
    {
         self::assertStringContainsString('<dt>what</dt>', $this->fieldListNodeRenderer->render());
    }
}
