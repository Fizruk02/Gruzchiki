<?php

namespace App\Actions;

use App\Models\OrdersFields;
use App\Models\RequestFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use LaravelViews\Actions\Action;
use LaravelViews\Views\View;
use Livewire\WithPagination;
use LaravelViews\Actions\Confirmable;
use App\Models\User;

class UpRequesFieldAction extends Action
{
    /**
     * Any title you want to be displayed
     * @var String
     * */
    public $title = "Вверх";

    /**
     * This should be a valid Feather icon string
     * @var String
     */
    public $icon = "arrow-up";

    /**
     * Execute the action when the user clicked on the button
     *
     * @param $model RequestFields object of the list where the user has clicked
     * @param $view Current view where the action was executed from
     */
    public function handle(Model $model, View $view)
    {
        $i = 1;
        $prev = null;
        $ofs = RequestFields::where('cabinet_id', $model->cabinet_id)->orderBy('sort')->get();
        foreach ($ofs as $of) {
            $of->sort = $i;
            if ($prev && ($of->id == $model->id)) {
                $of->sort = $prev->sort;
                $prev->sort = $i;
                $prev->save();
            }
            $of->save();
            $prev = $of;
            $i++;
        }
    }

    public function renderIf($item, View $view)
    {
        //if ($item->sort <= 1) return false;
        return true;
    }
}
