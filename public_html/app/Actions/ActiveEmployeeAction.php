<?php

namespace App\Actions;

use App\Models\Users;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use LaravelViews\Actions\Action;
use LaravelViews\Views\View;
use Livewire\WithPagination;
use LaravelViews\Actions\Confirmable;
use App\Models\User;

class ActiveEmployeeAction extends Action
{
    use Confirmable;

    /**
     * Any title you want to be displayed
     * @var String
     * */
    public $title = "В сотрудники";

    /**
     * This should be a valid Feather icon string
     * @var String
     */
    public $icon = "plus-circle";

    /**
     * This should be a valid Feather icon string
     * @var String
     */
    public $color = "blue";

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
            $model->status = Users::STATUS_ACTIVE;
            $model->role_id = 1;
            $model->save();
            $this->success('Сотрудник может получать заказы!');
        }
    }

    public function getConfirmationMessage($item = null)
    {
        return 'Хорошо подумал?';
    }

    public function renderIf($item, View $view)
    {
        return $item->status == Users::STATUS_WAIT;
    }
}
