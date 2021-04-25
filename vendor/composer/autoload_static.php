<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb5ffe74f9da22317f616e4c911eb3b08
{
    public static $prefixLengthsPsr4 = array (
        'x' => 
        array (
            'xingwenge\\canal_php\\' => 20,
        ),
        'S' => 
        array (
            'Socket\\Raw\\' => 11,
        ),
        'P' => 
        array (
            'Predis\\' => 7,
        ),
        'G' => 
        array (
            'Google\\Protobuf\\' => 16,
            'GPBMetadata\\Google\\Protobuf\\' => 28,
            'GPBMetadata\\' => 12,
        ),
        'C' => 
        array (
            'Com\\Alibaba\\Otter\\Canal\\Protocol\\' => 33,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'xingwenge\\canal_php\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'Socket\\Raw\\' => 
        array (
            0 => __DIR__ . '/..' . '/clue/socket-raw/src',
        ),
        'Predis\\' => 
        array (
            0 => __DIR__ . '/..' . '/predis/predis/src',
        ),
        'Google\\Protobuf\\' => 
        array (
            0 => __DIR__ . '/..' . '/google/protobuf/src/Google/Protobuf',
        ),
        'GPBMetadata\\Google\\Protobuf\\' => 
        array (
            0 => __DIR__ . '/..' . '/google/protobuf/src/GPBMetadata/Google/Protobuf',
        ),
        'GPBMetadata\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src/protocol/GPBMetadata',
        ),
        'Com\\Alibaba\\Otter\\Canal\\Protocol\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src/protocol/Com/Alibaba/Otter/Canal/Protocol',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb5ffe74f9da22317f616e4c911eb3b08::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb5ffe74f9da22317f616e4c911eb3b08::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitb5ffe74f9da22317f616e4c911eb3b08::$classMap;

        }, null, ClassLoader::class);
    }
}
