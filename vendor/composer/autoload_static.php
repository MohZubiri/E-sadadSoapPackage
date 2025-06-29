<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb8324d891e90725294c79b915f876f74
{
    public static $files = array (
        'fc4001700db28dae0eabdd51a1a81cb0' => __DIR__ . '/../..' . '/src/helpers.php',
    );

    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'MohZubiri\\ESadad\\' => 17,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'MohZubiri\\ESadad\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb8324d891e90725294c79b915f876f74::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb8324d891e90725294c79b915f876f74::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitb8324d891e90725294c79b915f876f74::$classMap;

        }, null, ClassLoader::class);
    }
}
