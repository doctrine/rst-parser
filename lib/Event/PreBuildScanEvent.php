<?php

declare(strict_types=1);

namespace Doctrine\RST\Event;

final class PreBuildScanEvent extends BuildEvent
{
    public const PRE_BUILD_SCAN = 'preBuildScan';
}
