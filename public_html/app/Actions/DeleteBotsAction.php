<?php

namespace App\Actions;

use App\Models\Bot;
use Illuminate\Support\Facades\Auth;
use LaravelViews\Actions\Action;
use LaravelViews\Views\View;
use App\Models\User;
use LaravelViews\Actions\Confirmable;

class DeleteBotsAction extends Action
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
     * @param Array $selectedModels Array with all the id of the selected models
     * @param $view Current view where the action was executed from
     */
    public function handle($selectedModels, View $view)
    {
        $cabinet = \App\Models\Cabinet::curCabinet();
        Bot::whereKey($selectedModels)->where('cabinet_id', $cabinet->id)->delete();
        $this->success('Боты удалены!');
    }

    public function getConfirmationMessage($item = null)
    {
        return 'Хорошо подумал?';
    }
}
