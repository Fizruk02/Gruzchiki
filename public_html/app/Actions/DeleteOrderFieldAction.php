<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use LaravelViews\Actions\Action;
use LaravelViews\Views\View;
use Livewire\WithPagination;
use LaravelViews\Actions\Confirmable;
use App\Models\User;

class DeleteOrderFieldAction extends Action
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
        if ($model->type == 'custom') {
            $model->delete();
            $this->success('Поле удалено!');
        } else {
            $this->error('Запрещено удалять системные поля!');
        }
    }

    public function getConfirmationMessage($item = null)
    {
        return 'Уверены что нужно удалить поле?';
    }

    public function renderIf($item, View $view)
    {
        if ($item->type != 'custom') return false;
        return true;
    }
}
