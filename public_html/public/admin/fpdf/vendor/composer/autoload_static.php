<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite13406bcd2a61ecc1a38414a26a1776b
{
    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'FPDF' => __DIR__ . '/..' . '/setasign/fpdf/fpdf.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInite13406bcd2a61ecc1a38414a26a1776b::$classMap;

        }, null, ClassLoader::class);
    }
}
