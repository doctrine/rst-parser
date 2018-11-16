<?php

declare(strict_types=1);

namespace Doctrine\RST\Event;

final class PostBuildRenderEvent extends BuildEvent
{
    public const POST_BUILD_RENDER = 'postBuildRender';
}
