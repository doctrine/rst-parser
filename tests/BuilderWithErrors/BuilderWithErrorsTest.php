<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\BuilderWithErrors;

use Doctrine\RST\Builder;
use Doctrine\Tests\RST\BaseBuilderTest;

class BuilderWithErrorsTest extends BaseBuilderTest
{
    protected function configureBuilder(Builder $builder): void
    {
        $builder->getConfiguration()->abortOnError(false);
        $builder->getConfiguration()->silentOnError(true);
    }

    public function testNoContentDirectiveError(): void
    {
        self::assertEquals(
            ['Error while processing "note" directive in "no_content_directive": Content expected, none found.'],
            $this->builder->getErrorManager()->getErrors()
        );
    }

    protected function getFixturesDirectory(): string
    {
        return 'BuilderWithErrors';
    }
}
