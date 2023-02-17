<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use LaravelViews\Actions\Action;
use LaravelViews\Views\View;
use Livewire\WithPagination;
use LaravelViews\Actions\Confirmable;
use App\Models\User;

class OpenOrderAction extends Action
{
    use Confirmable;

    /**
     * Any title you want to be displayed
     * @var String
     * */
    public $title = "Открыть";

    /**
     * This should be a valid Feather icon string
     * @var String
     */
    public $icon = "eye";

    /**
     * Execute the action when the user clicked on the button
     *
     * @param $model User object of the list where the user has clicked
     * @param $view Current view where the action was executed from
     */
    public function handle(Model $model, View $view)
    {
        $cabinet = \App\Models\Cabinet::curCabinet();
        if ($model->cabinet_id == $cabinet->id) {
            $model->active = 1;
            $model->save();
            $this->success('Заказ открыт!');
        }
    }

    public function getConfirmationMessage($item = null)
    {
        return 'Желаете открыть заказ?';
    }
}
