<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInitb7dce439a575ff5721c0c0e7d0a0abac
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        require __DIR__ . '/platform_check.php';

        spl_autoload_register(array('ComposerAutoloaderInitb7dce439a575ff5721c0c0e7d0a0abac', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInitb7dce439a575ff5721c0c0e7d0a0abac', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInitb7dce439a575ff5721c0c0e7d0a0abac::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
