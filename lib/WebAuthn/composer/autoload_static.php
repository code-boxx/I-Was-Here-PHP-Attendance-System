<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit8c9f25d2c3842b097ca941e347932e41
{
    public static $prefixLengthsPsr4 = array (
        'l' => 
        array (
            'lbuchs\\WebAuthn\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'lbuchs\\WebAuthn\\' => 
        array (
            0 => __DIR__ . '/..' . '/lbuchs/webauthn/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit8c9f25d2c3842b097ca941e347932e41::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit8c9f25d2c3842b097ca941e347932e41::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit8c9f25d2c3842b097ca941e347932e41::$classMap;

        }, null, ClassLoader::class);
    }
}
