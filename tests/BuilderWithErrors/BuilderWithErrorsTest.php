<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\BuilderWithErrors;

use Doctrine\Tests\RST\BaseBuilderTest;
use Symfony\Component\DomCrawler\Crawler;

use function trim;

class BuilderWithErrorsTest extends BaseBuilderTest
{
    public function testNoContentDirectiveError(): void
    {
        $contents = $this->getFileContents($this->targetFile('no_content_directive.html'));
        $crawler  = new Crawler($contents);
        $bodyHtml = trim($crawler->filter('body')->html());
        // the note is simply left out
        self::assertSame(<<<'EOF'
<p>Testing wrapper node at end of file</p>
<p>And here is more text.</p>
EOF
            , $bodyHtml);
    }

    protected function configureExpectedErrors(): void
    {
        $this->errorManager->expects(self::atLeastOnce())
            ->method('error')
            ->with('Error while processing "note" directive: "Content expected, none found."');
    }

    protected function getFixturesDirectory(): string
    {
        return 'BuilderWithErrors';
    }
}
