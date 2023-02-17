<?php

namespace App\Actions;

use App\Models\OrdersFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use LaravelViews\Actions\Action;
use LaravelViews\Actions\RedirectAction;
use LaravelViews\Views\View;
use Livewire\WithPagination;
use LaravelViews\Actions\Confirmable;
use App\Models\User;

class LandingAction extends RedirectAction
{
    /**
     * Any title you want to be displayed
     * @var String
     * */
    public $title = "Ленденг";

    /**
     * This should be a valid Feather icon string
     * @var String
     */
    public $icon = "globe";

    public function renderIf($item, View $view)
    {
        //dump($item);
        if (!$item->city || !$item->domain) return false;
        return true;
    }

    public function handle($item)
    {
        return redirect('/page/'.$item->domain);
    }
}
