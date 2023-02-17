<?php

namespace App\Filters;

use App\Models\Cabinet;
use Illuminate\Database\Eloquent\Builder;
use LaravelViews\Filters\Filter;

class UsersAdminFilter extends Filter
{
    public $title =  "Администратор";

    /**
     * Modify the current query when the filter is used
     *
     * @param Builder $query Current query
     * @param $value Value selected by the user
     * @return Builder Query modified
     */
    public function apply(Builder $query, $value, $request)
    {
        return $query->where('users.cabinet_id', $value);
    }

    /**
     * Defines the title and value for each option
     *
     * @return Array associative array with the title and values
     */
    public function options()
    {
        $cabinets = [];
        $admins = Cabinet::query()
            ->select(['cabinet.*', 'users.name'])
            ->join('users', 'cabinet.users_id', '=', 'users.id')
            ->with(['users'])->orderBy('name')->get();

        foreach ($admins as $admin) {
            $cabinets[$admin->users->name] = $admin->id;
        }
        return $cabinets;
    }
}
?>

