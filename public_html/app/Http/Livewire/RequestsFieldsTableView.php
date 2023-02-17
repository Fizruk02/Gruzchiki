<?php

namespace App\Http\Livewire;

use App\Actions\DeleteOrderFieldAction;
use App\Actions\DeleteRequestFieldAction;
use App\Actions\DeleteRequestsAction;
use App\Actions\DownOrderFieldAction;
use App\Actions\DownRequestFieldAction;
use App\Actions\UpOrderFieldAction;
use App\Actions\UpRequesFieldAction;
use App\Constructor\Facades\CRUI;
use App\Models\OrdersFields;
use App\Models\RequestFields;
use Illuminate\Database\Eloquent\Builder;
use LaravelViews\Facades\UI;
use LaravelViews\Views\TableView;
use LaravelViews\Views\Traits\WithAlerts;

class RequestsFieldsTableView extends TableView
{
    use WithAlerts;

    protected $num = 1;
    protected $model = RequestFields::class;
    public $cabinet_id = null;

    public $itemComponent = 'components.orders-field';

    protected $paginate = 2000;

    protected $listeners = ['add-field' => 'addField'];

    public function sayHello()
    {
        // your code here
    }

    public function render()
    {

        $items = $this->getRenderData();
        $data = array_merge(
            $items,
            [
                'view' => $this
            ]
        );

        return view("components.orders-field", $data);
    }

    public function headers(): array
    {
        $headers = [
            //'#',
            //'Порядок',
            'Имя',
            'Список',
            'Текст',
            'Номер',
            'Обязательно',
        ];

        return $headers;
    }

    public function row(RequestFields $fields)
    {
        $row = [
            //($this->page -  1) * $this->paginate + $this->num++,
            //UI::editable($fields, 'sort'),
            UI::editable($fields, 'name'),
            CRUI::check($fields, 'is_list'),
            CRUI::check($fields, 'is_text'),
            CRUI::check($fields, 'is_number'),
            CRUI::check($fields, 'is_required'),
        ];
        return $row;
    }

    public function addField($params) {
        $id = $params['id'];

        $cabinet = \App\Models\Cabinet::where('id', $id)->first();
        $of = new \App\Models\RequestFields();
        $of->cabinet_id = $id;
        $of->name = '-';
        $of->is_list = 0;
        $of->save();

        $this->success('Добавлено новое поле');
    }

    /**
     * Method fired by the `editable` component, it
     * gets the model instance and a key-value array
     * with the modified dadta
     */
    public function update(RequestFields $fields, $data)
    {
        /*if (isset($data['phone'])) {
            $cabinet->users->phone = $data['phone'];
            try {
                if ($cabinet->users->save()) $this->success('Телефон сохранен!');
                else $this->error('Не удались сохранить телефон!');
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }

        }*/
        if($fields->update($data)) $this->success('Успешно сохранено!');
        else $this->error('Не удались сохранить!');
    }

    /**
     * Sets a initial query with the data to fill the table
     *
     * @return Builder Eloquent query
     */
    public function repository(): Builder
    {
        $r = RequestFields::query()
            ->where('cabinet_id', $this->cabinet_id)
            ->orderBy('sort');

        return $r;
    }

    /** For actions by item */
    protected function actionsByRow()
    {
        return [
            new DeleteRequestFieldAction(),
            new DownRequestFieldAction(),
            new UpRequesFieldAction(),
        ];
    }

}
