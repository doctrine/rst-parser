<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\References;

use Doctrine\RST\Environment;
use Doctrine\RST\Utility\TitleLinkUtility;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TitleLinkUtilityTest extends TestCase
{
    /** @var Environment|MockObject */
    private $environment;

    /** @var TitleLinkUtility */
    private $titleLinkUtility;

    protected function setUp(): void
    {
        $this->environment      = $this->createMock(Environment::class);
        $this->titleLinkUtility = new TitleLinkUtility($this->environment, 999);
    }

    public function testBuildEmptyTitleLinksCreatesEmptyTocItemArray(): void
    {
        $tocItems = [];
        $this->titleLinkUtility->buildLevel('', [], 1, $tocItems, 'index');

        self::assertEquals($tocItems, []);
    }

    public function testBuildTitleLinks1Level(): void
    {
        $tocItems = [];
        $titles   = [
            ['My Title', []],
        ];
        $expected = [
            0 => [
                'targetId' => '',
                'targetUrl' => '',
                'title' => 'My Title',
                'level' => 1,
                'children' => [],
                'subpages' => [],
            ],
        ];
        $this->titleLinkUtility->buildLevel('', $titles, 1, $tocItems, 'index');

        self::assertEquals($tocItems, $expected);
    }

    public function testBuildTitleLinks2Level(): void
    {
        $tocItems = [];
        $titles   = [
            [
                'My Title',
                [
                    [
                        'Subtitle 1',
                        [],
                    ],
                    [
                        'Subtitle 2',
                        [],
                    ],
                ],
            ],
        ];
        $expected = [
            0 => [
                'targetId' => '',
                'targetUrl' => '',
                'title' => 'My Title',
                'level' => 1,
                'children' => [
                    0 => [
                        'targetId' => 'subtitle-1',
                        'targetUrl' => '',
                        'title' => 'Subtitle 1',
                        'level' => 2,
                        'children' => [],
                        'subpages' => [],
                    ],
                    1 => [
                        'targetId' => 'subtitle-2',
                        'targetUrl' => '',
                        'title' => 'Subtitle 2',
                        'level' => 2,
                        'children' => [],
                        'subpages' => [],
                    ],
                ],
                'subpages' => [],
            ],
        ];
        $this->titleLinkUtility->buildLevel('', $titles, 1, $tocItems, 'index');

        self::assertEquals($tocItems, $expected);
    }

    public function testBuildTitleLinks3Level(): void
    {
        $tocItems = [];
        $titles   = [
            [
                'My Title',
                [
                    [
                        'Subtitle 1',
                        [
                            [
                                'Subtitle 1 1',
                                [],
                            ],
                            [
                                'Subtitle 1 2',
                                [],
                            ],
                        ],
                    ],
                    [
                        'Subtitle 2',
                        [],
                    ],
                ],
            ],
        ];
        $expected = [
            0 => [
                'targetId' => '',
                'targetUrl' => '',
                'title' => 'My Title',
                'level' => 1,
                'children' => [
                    0 => [
                        'targetId' => 'subtitle-1',
                        'targetUrl' => '',
                        'title' => 'Subtitle 1',
                        'level' => 2,
                        'children' => [
                            0 => [
                                'targetId' => 'subtitle-1-1',
                                'targetUrl' => '',
                                'title' => 'Subtitle 1 1',
                                'level' => 3,
                                'children' => [],
                                'subpages' => [],
                            ],
                            1 => [
                                'targetId' => 'subtitle-1-2',
                                'targetUrl' => '',
                                'title' => 'Subtitle 1 2',
                                'level' => 3,
                                'children' => [],
                                'subpages' => [],
                            ],
                        ],
                        'subpages' => [],
                    ],
                    1 => [
                        'targetId' => 'subtitle-2',
                        'targetUrl' => '',
                        'title' => 'Subtitle 2',
                        'level' => 2,
                        'children' => [],
                        'subpages' => [],
                    ],
                ],
                'subpages' => [],
            ],
        ];
        $this->titleLinkUtility->buildLevel('', $titles, 1, $tocItems, 'index');

        self::assertEquals($tocItems, $expected);
    }

    public function testBuildTitleLinks3LevelMaxdepth2(): void
    {
        $titleLinkUtilityMaxDepth2 = new TitleLinkUtility($this->environment, 2);
        $tocItems                  = [];
        $titles                    = [
            [
                'My Title',
                [
                    [
                        'Subtitle 1',
                        [
                            [
                                'Subtitle 1 1',
                                [],
                            ],
                            [
                                'Subtitle 1 2',
                                [],
                            ],
                        ],
                    ],
                    [
                        'Subtitle 2',
                        [],
                    ],
                ],
            ],
        ];
        $expected                  = [
            0 => [
                'targetId' => '',
                'targetUrl' => '',
                'title' => 'My Title',
                'level' => 1,
                'children' => [
                    0 => [
                        'targetId' => 'subtitle-1',
                        'targetUrl' => '',
                        'title' => 'Subtitle 1',
                        'level' => 2,
                        'children' => [],
                        'subpages' => [],
                    ],
                    1 => [
                        'targetId' => 'subtitle-2',
                        'targetUrl' => '',
                        'title' => 'Subtitle 2',
                        'level' => 2,
                        'children' => [],
                        'subpages' => [],
                    ],
                ],
                'subpages' => [],
            ],
        ];
        $titleLinkUtilityMaxDepth2->buildLevel('', $titles, 1, $tocItems, 'index');

        self::assertEquals($tocItems, $expected);
    }

    public function testBuildTitleLinks2LevelWithUrl(): void
    {
        $this->environment->expects(self::atLeastOnce())
            ->method('generateUrl')
            ->willReturn('generated_url');
        $tocItems = [];
        $titles   = [
            [
                'My Title',
                [
                    [
                        'Subtitle 1',
                        [],
                    ],
                    [
                        'Subtitle 2',
                        [],
                    ],
                ],
            ],
        ];
        $expected = [
            0 => [
                'targetId' => 'index',
                'targetUrl' => 'generated_url',
                'title' => 'My Title',
                'level' => 1,
                'children' => [
                    0 => [
                        'targetId' => 'index-subtitle-1',
                        'targetUrl' => 'generated_url',
                        'title' => 'Subtitle 1',
                        'level' => 2,
                        'children' => [],
                        'subpages' => [],
                    ],
                    1 => [
                        'targetId' => 'index-subtitle-2',
                        'targetUrl' => 'generated_url',
                        'title' => 'Subtitle 2',
                        'level' => 2,
                        'children' => [],
                        'subpages' => [],
                    ],
                ],
                'subpages' => [],
            ],
        ];
        $this->titleLinkUtility->buildLevel('index', $titles, 1, $tocItems, 'index');

        self::assertEquals($tocItems, $expected);
    }
}
