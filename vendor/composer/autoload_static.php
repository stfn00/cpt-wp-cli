<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit1f421a01455b5e840cb633c129abb5b9
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'Doctrine\\Inflector\\' => 19,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Doctrine\\Inflector\\' => 
        array (
            0 => __DIR__ . '/..' . '/doctrine/inflector/lib/Doctrine/Inflector',
        ),
    );

    public static $prefixesPsr0 = array (
        'M' => 
        array (
            'Mustache' => 
            array (
                0 => __DIR__ . '/..' . '/mustache/mustache/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit1f421a01455b5e840cb633c129abb5b9::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit1f421a01455b5e840cb633c129abb5b9::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit1f421a01455b5e840cb633c129abb5b9::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}