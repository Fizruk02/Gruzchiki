<?php

namespace App\Actions;

use App\Models\OrdersFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use LaravelViews\Actions\Action;
use LaravelViews\Views\View;
use Livewire\WithPagination;
use LaravelViews\Actions\Confirmable;
use App\Models\User;

class DownOrderFieldAction extends Action
{
    /**
     * Any title you want to be displayed
     * @var String
     * */
    public $title = "Вниз";

    /**
     * This should be a valid Feather icon string
     * @var String
     */
    public $icon = "arrow-down";

    /**
     * Execute the action when the user clicked on the button
     *
     * @param $model User object of the list where the user has clicked
     * @param $view Current view where the action was executed from
     */
    public function handle(Model $model, View $view)
    {
        $i = 1;
        $prev = null;
        $ofs = OrdersFields::where('cabinet_id', $model->cabinet_id)->orderBy('sort')->get();
        foreach ($ofs as $of) {
            $of->sort = $i;
            if ($of->id == $model->id) {
                $prev = $of;
            } else if ($prev) {
                $of->sort = $i - 1;
                $prev->sort = $i;
                $prev->save();
                $prev = null;
            }

            $of->save();
            $i++;
        }
    }

    public function renderIf($item, View $view)
    {
        //if ($item->sort <= 1) return false;
        return true;
    }
}
