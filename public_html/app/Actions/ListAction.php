<?php

namespace App\Actions;

use LaravelViews\Actions\Action;
use LaravelViews\Views\View;
use Livewire\WithPagination;
use LaravelViews\Actions\Confirmable;
use App\Models\WebPages;

class ListAction extends Action
{
    /**
     * Any title you want to be displayed
     * @var String
     * */
    public $title = "Список";

    /**
     * This should be a valid Feather icon string
     * @var String
     */
    public $icon = "list";

    /**
     * Execute the action when the user clicked on the button
     *
     * @param $model User object of the list where the user has clicked
     * @param $view Current view where the action was executed from
     */
    public function handle(WebPages $model, View $view)
    {
        $segments = request()->segments();
        unset($segments[count($segments) - 1]);
        unset($segments[count($segments) - 1]);
        return redirect()->to(implode('/', $segments));
    }

}
