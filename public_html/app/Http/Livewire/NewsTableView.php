<?php

namespace App\Http\Livewire;

use App\Actions\DeleteNewsAction;
use App\Models\Cabinet;
use App\Models\News;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use LaravelViews\Actions\RedirectAction;
use Illuminate\Database\Eloquent\Builder;
use LaravelViews\Facades\Header;
use LaravelViews\Facades\UI;
use LaravelViews\Views\TableView;
use LaravelViews\Views\Traits\WithAlerts;

class NewsTableView extends TableView
{
    use WithAlerts;

    protected $num = 1;
    protected $model = News::class;
    protected $cabinet = null;

    protected $paginate = 20;

    public $searchBy = ['title'];

    public function getCabinet() {
        if ($this->cabinet) return $this->cabinet;
        return $this->cabinet = \App\Models\Cabinet::curCabinet();
    }

    public function headers(): array
    {
        return [
            '#',
            Header::title('Новость')->sortBy('title'),
            Header::title('Бот')->sortBy('bot_id'),
            Header::title('Дата')->sortBy('created_at'),
        ];
    }

    public function row(News $news)
    {
        return [
            ($this->page -  1) * $this->paginate + $this->num++,
            UI::link($news->title, route('news-edit', $news)), //$user->name,
            $news->bot->name,
            $news->created_at,
        ];
    }

    /**
     * Sets a initial query with the data to fill the table
     *
     * @return Builder Eloquent query
     */
    public function repository(): Builder
    {
        $cabinet = $this->getCabinet();
        $n = News::query()->where('cabinet_id', $cabinet->id);
        if (!$this->sortBy) $n->orderBy('created_at', 'desc');
        return $n;
    }

    /** For actions by item */
    protected function actionsByRow()
    {
        return [
            new DeleteNewsAction(),
        ];
    }

    /** For bulk actions */
    protected function bulkActions()
    {
        return [
            //new DeleteRequestsAction(),
        ];
    }

    protected function filters()
    {
        return [
            //new UsersActiveFilter,
        ];
    }
}
