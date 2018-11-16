<?php

declare(strict_types=1);

namespace Doctrine\RST\Event;

final class PreBuildRenderEvent extends BuildEvent
{
    public const PRE_BUILD_RENDER = 'preBuildRender';
}
