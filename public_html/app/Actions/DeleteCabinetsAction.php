<?php

namespace App\Actions;

use App\Models\Cabinet;
use LaravelViews\Actions\Action;
use LaravelViews\Views\View;
use App\Models\User;
use LaravelViews\Actions\Confirmable;

class DeleteCabinetsAction extends Action
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
        // Your code here
        foreach (Cabinet::whereKey($selectedModels) as $cabinet){
            $cabinet->user()->delete();
            $cabinet->delete();
        }
        $this->success('Кабинеты удалены!');
    }

    public function getConfirmationMessage($item = null)
    {
        return 'Хорошо подумал?';
    }
}
