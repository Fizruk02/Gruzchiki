<?php

namespace App\Actions;

use LaravelViews\Actions\Action;
use LaravelViews\Views\View;
use App\Models\User;

class ActivateUsersAction extends Action
{
    /**
     * Any title you want to be displayed
     * @var String
     * */
    public $title = "Активировать";

    /**
     * This should be a valid Feather icon string
     * @var String
     */
    public $icon = "shield";

    /**
     * Execute the action when the user clicked on the button
     *
     * @param Array $selectedModels Array with all the id of the selected models
     * @param $view Current view where the action was executed from
     */
    public function handle($selectedModels, View $view)
    {
        // Your code here
        User::whereKey($selectedModels)->update([
            'type' => 'admin'
        ]);
    }
}
