<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use LaravelViews\Actions\Action;
use LaravelViews\Views\View;
use Livewire\WithPagination;
use LaravelViews\Actions\Confirmable;
use App\Models\User;

class DeleteOrderAction extends Action
{
    use Confirmable;

    /**
     * Any title you want to be displayed
     * @var String
     * */
    public $title = "Удалить";

    /**
     * This should be a valid Feather icon string
     * @var String
     */
    public $icon = "trash";

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
            $model->delete();
            $this->success('Заказ удален!');
        }
    }

    public function getConfirmationMessage($item = null)
    {
        return 'Уверены что нужно удалить заказ?';
    }
}
