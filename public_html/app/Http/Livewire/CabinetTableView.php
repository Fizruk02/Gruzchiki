<?php

namespace App\Http\Livewire;

use App\Actions\DeleteCabinetAction;
use App\Actions\DeleteCabinetsAction;
use App\Actions\LandingAction;
use App\Filters\CityFilter;
use App\Models\Cabinet;
use App\Models\Orders;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use LaravelViews\Actions\RedirectAction;
use LaravelViews\Views\TableView;
use Illuminate\Database\Eloquent\Builder;
use LaravelViews\Facades\Header;
use LaravelViews\Facades\UI;
use LaravelViews\Views\Traits\WithAlerts;

class CabinetTableView extends TableView
{
    use WithAlerts;

    protected $num = 1;
    protected $model = Cabinet::class;

    protected $paginate = 20;

    public $searchBy = ['users.name', 'users.phone'];

    public function headers(): array
    {
        return [
            '#',
            //Header::title('ID')->sortBy('id'),
            Header::title('ФИО')->sortBy('users.name'),
            Header::title('Телефон')->sortBy('users.phone'),
            Header::title('Город')->sortBy('city'),
            Header::title('Дата окончания')->sortBy('finish_at'),
            Header::title('Продлить работу кабинета'),
        ];
    }

    public function row(Cabinet $cabinet)
    {
        return [
            ($this->page -  1) * $this->paginate + $this->num++,
            //$user->id,
            UI::link($cabinet->users->name, route('cabinet-edit', $cabinet->id)), //$user->name,
            //UI::editable($user, 'email'),
            //$user->email,
            //$cabinet->user->phone,
            UI::editable($cabinet, 'phone'),
            $cabinet->city,
            //$user->status ? UI::icon('check', 'success') : '',
            //$user->created_at,
            //$user->updated_at
            date('d.m.Y', strtotime($cabinet->finish_at)),

            '<a wire:click.prevent="setProlong('.$cabinet->id.', 1)" href="'.route('cabinet-plus', $cabinet->id).'?plus=1" class="whitespace-no-wrap">'.UI::badge('месяц').'</a> '.
            '<a wire:click.prevent="setProlong('.$cabinet->id.', 3)" href="'.route('cabinet-plus', $cabinet->id).'?plus=3" class="whitespace-no-wrap">'.UI::badge('3 месяца').'</a> '.
            '<a wire:click.prevent="setProlong('.$cabinet->id.', 6)" href="'.route('cabinet-plus', $cabinet->id).'?plus=6" class="whitespace-no-wrap">'.UI::badge('6 месяцев').'</a> '.
            '<a wire:click.prevent="setProlong('.$cabinet->id.', 9)" href="'.route('cabinet-plus', $cabinet->id).'?plus=9" class="whitespace-no-wrap">'.UI::badge('9 месяцев').'</a> '.
            '<a wire:click.prevent="setProlong('.$cabinet->id.', 12)" href="'.route('cabinet-plus', $cabinet->id).'?plus=12" class="whitespace-no-wrap">'.UI::badge('год').'</a> '
        ];
    }

    public function setProlong($id, $time)
    {
        if (Auth::user()->id_cms_privileges == Users::ROLE_SUPERADMIN) {
            $cabinet = Cabinet::where('id', $id)->first();
            $dateAt = strtotime('+'.$time.' MONTH', strtotime($cabinet->finish_at));
            $cabinet->finish_at = date('Y-m-d H:i:s', $dateAt);
            if($cabinet->save()) $this->success('Кабинет '.$cabinet->users->name.' продлен.');
        }
    }

    /**
     * Sets a initial query with the data to fill the table
     *
     * @return Builder Eloquent query
     */
    public function repository(): Builder
    {
        //return Cabinet::query();
        /*return UserAdmin::query()
            ->join('cabinet', 'users.id', '=', 'cabinet.user_id')
            ->where('id_cms_privileges', 3)
            ->applyScopes();*/
        /*return Cabinet::query()->select(['cabinet.*', 'users.name', 'users.phone'])
            ->join('users', 'users.id', '=', 'cabinet.users_id')
            ->applyScopes();*/
        return Cabinet::query()->select(['cabinet.*', 'users.name', 'users.phone'])->with('users')
            ->join('users', 'users.id', '=', 'cabinet.users_id')
            ->applyScopes();
    }

    /** For actions by item */
    protected function actionsByRow()
    {
        return [
            new LandingAction('landing', 'Лендинг', 'globe'),
            new RedirectAction('admin-landing-edit', 'Поменять лендинг', 'codepen'),
            new RedirectAction('orders-fields', 'Заказ', 'command'),
            new RedirectAction('request-fields', 'Заявка', 'aperture'),
            new DeleteCabinetAction(),
            //new RedirectAction('cabinets', 'Просмотр', 'eye'),
        ];
    }

    /** For bulk actions */
    protected function bulkActions()
    {
        return [
            //new ActivateUsersAction,
            //new DeleteUsersAction(),
            new DeleteCabinetsAction(),
        ];
    }

    /**
     * Method fired by the `editable` component, it
     * gets the model instance and a key-value array
     * with the modified dadta
     */
    public function update(Cabinet $cabinet, $data)
    {
        if (isset($data['phone'])) {
            $cabinet->users->phone = $data['phone'];
            try {
                if ($cabinet->users->save()) $this->success('Телефон сохранен!');
                else $this->error('Не удались сохранить телефон!');
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }

        }
        //$user->update($data);
    }

    protected function filters()
    {
        return [
            new CityFilter(),
        ];
    }
}
