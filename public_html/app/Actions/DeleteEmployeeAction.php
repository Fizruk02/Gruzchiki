<?php

namespace App\Actions;

use App\Models\Users;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use LaravelViews\Actions\Action;
use LaravelViews\Views\Traits\WithAlerts;
use LaravelViews\Views\View;
use Livewire\WithPagination;
use LaravelViews\Actions\Confirmable;
use App\Models\User;

class DeleteEmployeeAction extends Action
{
    use Confirmable;
    use WithAlerts;

    /**
     * Any title you want to be displayed
     * @var String
     * */
    public $title = "Удалить";

    public $is_redirect = false;

    /**
     * This should be a valid Feather icon string
     * @var String
     */
    public $icon = "trash";

    public $is_hidden = true;

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
            $model->is_deleted = true;
            $model->role_id = 0;
            $model->save();
            //$model->delete();
            $this->success('Сотрудник удален!');

            //if ($this->is_redirect)
            //    return redirect()->route('employees');
        }
    }

    public function getConfirmationMessage($item = null)
    {
        return 'Хорошо подумал?';
    }

    public function renderIf($item, View $view)
    {
        return ($item->status != Users::STATUS_BAN) && ($item->is_deleted != 1);
    }
}
