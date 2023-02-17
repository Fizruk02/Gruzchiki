<?php

namespace App\Actions;

use App\Models\RequestFields;
use Illuminate\Database\Eloquent\Model;
use LaravelViews\Actions\Action;
use LaravelViews\Views\View;
use LaravelViews\Actions\Confirmable;

class DeleteRequestFieldAction extends Action
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
     * @param $model RequestFields object of the list where the user has clicked
     * @param $view Current view where the action was executed from
     */
    public function handle(Model $model, View $view)
    {
        $model->delete();
        $this->success('Поле удалено!');
    }

    public function getConfirmationMessage($item = null)
    {
        return 'Уверены что нужно удалить поле? Данное поле удалится из всех существующих заявок';
    }

    public function renderIf($item, View $view)
    {
        return true;
    }
}
