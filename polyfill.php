<?php

if (!class_exists(Symfony\Component\Filesystem\Path::class)) {
    class Symfony_Component_Filesystem_Path_Polyfill {
        public static function normalize($path): string
        {
            return str_replace('\\', '/', $path);
        }
    }
    class_alias(
        Symfony_Component_Filesystem_Path_Polyfill::class,
        Symfony\Component\Filesystem\Path::class
    );
}
