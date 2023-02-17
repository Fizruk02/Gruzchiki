<?php

namespace App\Filters;

use App\Models\Users;
use Illuminate\Database\Eloquent\Builder;
use LaravelViews\Filters\Filter;

class UsersStatusFilter extends Filter
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
            'Активные' => Users::STATUS_ACTIVE,
            'Одобренные' => Users::STATUS_WAIT,
            'Новые' => Users::STATUS_NEW,
            'Черный список' => Users::STATUS_BLACK,
            //'Заблокированные' => -20,
        ];
    }
}
?>

