<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb7dce439a575ff5721c0c0e7d0a0abac
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'MEC\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'MEC\\' => 
        array (
            0 => __DIR__ . '/../..' . '/core/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'I' => 
        array (
            'ICal' => 
            array (
                0 => __DIR__ . '/..' . '/johngrogg/ics-parser/src',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb7dce439a575ff5721c0c0e7d0a0abac::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb7dce439a575ff5721c0c0e7d0a0abac::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitb7dce439a575ff5721c0c0e7d0a0abac::$prefixesPsr0;
            $loader->classMap = ComposerStaticInitb7dce439a575ff5721c0c0e7d0a0abac::$classMap;

        }, null, ClassLoader::class);
    }
}
