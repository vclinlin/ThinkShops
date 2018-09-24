<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit8958a6687d7e2cb0d6075770073972d3
{
    public static $prefixLengthsPsr4 = array (
        't' => 
        array (
            'think\\composer\\' => 15,
        ),
        'a' => 
        array (
            'app\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'think\\composer\\' => 
        array (
            0 => __DIR__ . '/..' . '/topthink/think-installer/src',
        ),
        'app\\' => 
        array (
            0 => __DIR__ . '/../..' . '/application',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit8958a6687d7e2cb0d6075770073972d3::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit8958a6687d7e2cb0d6075770073972d3::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
