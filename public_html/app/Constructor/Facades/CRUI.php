<?php

namespace App\Constructor\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string component($view, $data)
 * @method static string attributes($data)
 *
 * @see \LaravelViews\UI\UI
 */
class CRUI extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'crui';
    }
}
