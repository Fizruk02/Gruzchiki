<?php

use App\Constructor\CRUI\Variants;

if (!function_exists('variants')) {
    function variants($path = null)
    {
        $variants = new Variants($path);
        if ($path) {
            return $variants->class();
        }

        return $variants;
    }
}
