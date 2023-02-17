<?php

namespace App\Filters;

use App\Models\Cabinet;
use App\Models\Orders;
use App\Models\Users;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use LaravelViews\Filters\Filter;

class OrderActiveFilter extends Filter
{
    public $title =  "Статус";

    /**
     * Modify the current query when the filter is used
     *
     * @param Builder $query Current query
     * @param $value Value selected by the user
     * @return Builder Query modified
     */
    public function apply(Builder $query, $value, $request)
    {
        $cabinet = Cabinet::curCabinet();
        return $query->where('orders.active', $value)->where('cabinet_id', $cabinet->id);
    }

    /**
     * Defines the title and value for each option
     *
     * @return Array associative array with the title and values
     */
    public function options()
    {
        $filters = [
            'Активные' => 1,
            'Не активные' => 0,
        ];
        return $filters;
    }
}
?>

