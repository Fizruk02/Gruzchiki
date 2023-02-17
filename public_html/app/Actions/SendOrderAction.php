<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use LaravelViews\Actions\Action;
use LaravelViews\Views\View;
use Livewire\WithPagination;
use LaravelViews\Actions\Confirmable;
use App\Models\User;

class SendOrderAction extends Action
{
    use Confirmable;

    /**
     * Any title you want to be displayed
     * @var String
     * */
    public $title = "Разослать";

    /**
     * This should be a valid Feather icon string
     * @var String
     */
    public $icon = "mail";

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
            $model->status = 0;
            $model->active = 1;
            $model->type_send = 1;
            $model->mailings_id = 0;
            $model->save();
            $this->success('Заказ разослан!');
        }
    }

    public function getConfirmationMessage($item = null)
    {
        return 'Желаете заново разослать заказ?';
    }
}
