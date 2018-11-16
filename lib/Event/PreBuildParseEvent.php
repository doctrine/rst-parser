<?php

declare(strict_types=1);

namespace Doctrine\RST\Event;

final class PreBuildParseEvent extends BuildEvent
{
    public const PRE_BUILD_PARSE = 'preBuildParse';
}
