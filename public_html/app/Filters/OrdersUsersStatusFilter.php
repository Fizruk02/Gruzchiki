<?php

namespace App\Filters;

use App\Models\Users;
use Illuminate\Database\Eloquent\Builder;
use LaravelViews\Filters\Filter;

class OrdersUsersStatusFilter extends Filter
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
        if ($value == 3) return $query->where('orders_users.status', '>=', $value);
        return $query->where('orders_users.status', $value);
    }

    /**
     * Defines the title and value for each option
     *
     * @return Array associative array with the title and values
     */
    public function options()
    {
        return [
            'Отклонили' => -1,
            'Приняли' => 3,
        ];
        /*
        * 1 - дошло
        * 2 - согласился первый раз
        * 3 - согласился второй раз, прожал ДА 3 раза
        * 4 - одобрен на заказ
        * 5 - выехал
        * 6 - на месте
        * 7 - выполнил заказ
        * 8 - получил оплату
        * 100 - возникла проблема
        * */
    }
}
?>

