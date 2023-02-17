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

class BlackEmployeeAction extends Action
{
    use Confirmable;

    /**
     * Any title you want to be displayed
     * @var String
     * */
    public $title = "В черный список";

    /**
     * This should be a valid Feather icon string
     * @var String
     */
    public $icon = "alert-circle";

    /**
     * This should be a valid Feather icon string
     * @var String
     */
    public $color = "black";

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
            if ($model->status == Users::STATUS_BLACK) {
                $model->black_at = 0;
                $model->status = Users::STATUS_ACTIVE;
                $msg = 'Сотрудник удален из черного списка';
                $model->sendMessage('Дисциплинарное наказание снято. Вам снова доступно участие в заказах.');
            } else {
                $model->black_at = time();
                $model->status = Users::STATUS_BLACK;
                $msg = 'Сотрудник добавлен в черный список';
                $model->sendMessage('К вам применено дисциплинарное наказание. Вам доступен только просмотр заказов.');
            }
            $model->save();
            //$model->delete();
            $this->success($msg);
        }
    }

    public function getConfirmationMessage($item = null)
    {
        return 'Хорошо подумал?';
    }

    public function renderIf($item, View $view)
    {
        if ($item->status == Users::STATUS_BLACK) {
            $this->title = 'Убрать из черного списка';
            $this->color = 'yellow';
            return true;
        }

        $this->title = 'В черный список';
        $this->color = 'black';

        return ($item->status != Users::STATUS_NEW) && ($item->status != Users::STATUS_WAIT);
    }
}
