<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit03dbc833f7da22623265828b6e6be034
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPHtmlParser\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPHtmlParser\\' => 
        array (
            0 => __DIR__ . '/..' . '/paquettg/php-html-parser/src/PHPHtmlParser',
        ),
    );

    public static $prefixesPsr0 = array (
        's' => 
        array (
            'stringEncode' => 
            array (
                0 => __DIR__ . '/..' . '/paquettg/string-encode/src',
            ),
        ),
    );

    public static $fallbackDirsPsr0 = array (
        0 => __DIR__ . '/..' . '/imelgrat/barcode-validator/src',
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit03dbc833f7da22623265828b6e6be034::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit03dbc833f7da22623265828b6e6be034::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit03dbc833f7da22623265828b6e6be034::$prefixesPsr0;
            $loader->fallbackDirsPsr0 = ComposerStaticInit03dbc833f7da22623265828b6e6be034::$fallbackDirsPsr0;
            $loader->classMap = ComposerStaticInit03dbc833f7da22623265828b6e6be034::$classMap;

        }, null, ClassLoader::class);
    }
}
