<?php

namespace App\Filters;

use App\Models\Bot;
use App\Models\Cabinet;
use App\Models\Orders;
use App\Models\Users;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use LaravelViews\Filters\Filter;

class CityFilter extends Filter
{
    public $title =  "Город";

    /**
     * Modify the current query when the filter is used
     *
     * @param Builder $query Current query
     * @param $value Value selected by the user
     * @return Builder Query modified
     */
    public function apply(Builder $query, $value, $request)
    {
        return $query->where('city', $value);
    }

    /**
     * Defines the title and value for each option
     *
     * @return Array associative array with the title and values
     */
    public function options()
    {
        $filters = [];
        $cityes = Cabinet::select('city')->distinct()->get();
        foreach ($cityes as $city) {
            if(!$city->city) continue;
            $filters[$city->city] = $city->city;
        }
        return $filters;
    }
}
?>

