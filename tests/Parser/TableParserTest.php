<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\Parser;

use Doctrine\RST\Nodes\TableNode;
use Doctrine\RST\Parser;
use PHPUnit\Framework\TestCase;

class TableParserTest extends TestCase
{
    /**
     * @dataProvider getLineSeparatorsData
     */
    public function testParseTableLineSeparator(string $inputLine, ?Parser\TableSeparatorLineConfig $expectedConfig) : void
    {
        $tableParser           = new Parser\TableParser();
        $actualSeparatorConfig = $tableParser->parseTableSeparatorLine($inputLine);

        // equals to compare object data, not identity
        self::assertEquals($expectedConfig, $actualSeparatorConfig);
    }

    /**
     * @return mixed[]
     */
    public function getLineSeparatorsData() : array
    {
        return [
            [
                'FOO',
                null,
            ],
            [
                '=== === ===',
                new Parser\TableSeparatorLineConfig(
                    false,
                    TableNode::TYPE_SIMPLE,
                    [[0, 3], [4, 7], [8, 11]],
                    '=',
                    '=== === ==='
                ),
            ],
            [
                '=== ===== ===',
                new Parser\TableSeparatorLineConfig(
                    false,
                    TableNode::TYPE_SIMPLE,
                    [[0, 3], [4, 9], [10, 13]],
                    '=',
                    '=== ===== ==='
                ),
            ],
            [
                '===   =====   === ===',
                new Parser\TableSeparatorLineConfig(
                    false,
                    TableNode::TYPE_SIMPLE,
                    [[0, 3], [6, 11], [14, 17], [18, 21]],
                    '=',
                    '===   =====   === ==='
                ),
            ],
            [
                // not a valid first/last, but it
                // *is* valid to have these inside a simple table
                '--- --- ---',
                new Parser\TableSeparatorLineConfig(
                    false,
                    TableNode::TYPE_SIMPLE,
                    [[0, 3], [4, 7], [8, 11]],
                    '-',
                    '--- --- ---'
                ),
            ],
            [
                '--- -=- ---',
                null,
            ],
            [
                '+-----+------+--------+',
                new Parser\TableSeparatorLineConfig(
                    false,
                    TableNode::TYPE_PRETTY,
                    [[1, 6], [7, 13], [14, 22]],
                    '-',
                    '+-----+------+--------+'
                ),
            ],
        ];
    }
}
