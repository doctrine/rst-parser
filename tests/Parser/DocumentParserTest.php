<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\Parser;

use Doctrine\Common\EventManager;
use Doctrine\RST\Configuration;
use Doctrine\RST\Directives\Directive;
use Doctrine\RST\Environment;
use Doctrine\RST\ErrorManager;
use Doctrine\RST\NodeFactory\NodeFactory;
use Doctrine\RST\Parser;
use Doctrine\RST\Parser\DocumentParser;
use Exception;
use PHPUnit\Framework\TestCase;

class DocumentParserTest extends TestCase
{
    public function testErrorWhenDirectiveThrowsException(): void
    {
        $parser             = $this->createMock(Parser::class);
        $environment        = $this->createMock(Environment::class);
        $configuration      = $this->createMock(Configuration::class);
        $nodeFactory        = $this->createMock(NodeFactory::class);
        $eventManager       = $this->createMock(EventManager::class);
        $codeBlockDirective = $this->createMock(Directive::class);
        $errorManager       = $this->createMock(ErrorManager::class);

        $docParser = new DocumentParser(
            $configuration,
            $parser,
            $environment,
            $nodeFactory,
            $eventManager,
            ['code-block' => $codeBlockDirective],
            true,
            __DIR__
        );

        $codeBlockDirective->expects(self::once())
            ->method('process')
            ->willThrowException(new Exception('Invalid something something!'));
        $codeBlockDirective->expects(self::once())
            ->method('getName')
            ->willReturn('code-block-name');

        $environment
            ->method('getConfiguration')
            ->willReturn($configuration);

        $configuration->expects(self::once())
            ->method('getErrorManager')
            ->willReturn($errorManager);

        $errorManager->expects(self::once())
            ->method('error')
            ->with('Error while processing "code-block-name" directive: "Invalid something something!"');

        $docParser->parse('.. code-block:: php');
    }
}
