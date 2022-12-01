<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\BuilderWithErrors;

use Doctrine\RST\Builder;
use Doctrine\Tests\RST\BaseBuilderTest;
use Symfony\Component\DomCrawler\Crawler;

use function trim;

class BuilderWithErrorsTest extends BaseBuilderTest
{
    protected function configureBuilder(Builder $builder): void
    {
        $builder->getConfiguration()->abortOnError(false);
        $builder->getConfiguration()->silentOnError(true);
    }

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

        self::assertEquals(
            'Error while processing "note" directive: "Content expected, none found." in file "no_content_directive" at line 6',
            $this->builder->getErrorManager()->getErrors()[0]->asString()
        );
    }

    protected function getFixturesDirectory(): string
    {
        return 'BuilderWithErrors';
    }
}
