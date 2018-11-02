<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Builder;
use Throwable;

class BuilderInvalidReferenceTest extends BaseBuilderTest
{
    protected function setUp() : void
    {
        $this->builder = new Builder();
    }

    public function testUnresolvedReference() : void
    {
        $this->expectException(Throwable::class);
        $this->expectExceptionMessage('Found invalid reference "does_not_exist" in file "index"');

        $this->builder->build($this->sourceFile(), $this->targetFile());
    }

    protected function getFixturesDirectory() : string
    {
        return 'builder-invalid-reference';
    }
}
