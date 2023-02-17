<?php

namespace App\Http\Livewire;

use App\Actions\DeleteBotAction;
use App\Actions\DeleteBotsAction;
use App\Models\Bot;
use Illuminate\Support\Facades\Auth;
use LaravelViews\Views\TableView;
use Illuminate\Database\Eloquent\Builder;
use LaravelViews\Facades\Header;
use LaravelViews\Facades\UI;
use LaravelViews\Views\Traits\WithAlerts;
use LaravelViews\Actions\RedirectAction;

class BotsTableView extends TableView
{
    use WithAlerts;

    protected $num = 1;
    protected $model = Bot::class;

    protected $paginate = 20;

    public $searchBy = ['name'];

    public function headers(): array
    {
        return [
            '#',
            //Header::title('ID')->sortBy('id'),
            Header::title('Название')->sortBy('name'),
        ];
    }

    public function row(Bot $bot)
    {
        return [
            ($this->page -  1) * $this->paginate + $this->num++,
            UI::link($bot->name, route('bot-edit', $bot)),
        ];
    }

    /**
     * Sets a initial query with the data to fill the table
     *
     * @return Builder Eloquent query
     */
    public function repository(): Builder
    {
        $cabinet = \App\Models\Cabinet::curCabinet();
        return Bot::query()->where('cabinet_id', $cabinet->id);
    }

    /** For actions by item */
    protected function actionsByRow()
    {
        return [
            new DeleteBotAction(),
        ];
    }

    /** For bulk actions */
    protected function bulkActions()
    {
        return [
            //new ActivateUsersAction,
            new DeleteBotsAction(),
        ];
    }

    protected function filters()
    {
        return [
            //new UsersActiveFilter,
            //new CreatedFilter,
            //new UsersTypeFilter
        ];
    }
}
