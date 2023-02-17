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

class BanEmployeeAction extends Action
{
    use Confirmable;

    /**
     * Any title you want to be displayed
     * @var String
     * */
    public $title = "Забанить";

    /**
     * This should be a valid Feather icon string
     * @var String
     */
    public $icon = "eye-off";

    /**
     * This should be a valid Feather icon string
     * @var String
     */
    public $color = "red";

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
            $model->black_at = time();
            $model->status = Users::STATUS_BAN;
            $model->save();
            //$model->delete();
            $this->success('Сотрудник заблокирован!');
        }
    }

    public function getConfirmationMessage($item = null)
    {
        return 'Хорошо подумал?';
    }
}
