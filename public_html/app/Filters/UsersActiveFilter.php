<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use LaravelViews\Filters\Filter;

class UsersActiveFilter extends Filter
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
        return $query->where('users.status', $value);
    }

    /**
     * Defines the title and value for each option
     *
     * @return Array associative array with the title and values
     */
    public function options()
    {
        return [
            'Активные' => 0,
            'Новые' => -1,
            'Черный список' => -10,
            'Заблокированные' => -20,
        ];
    }
}
?>

