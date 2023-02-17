<?php

namespace App\Http\Livewire;

use App\Actions\DeleteOrderFieldAction;
use App\Actions\DownOrderFieldAction;
use App\Actions\UpOrderFieldAction;
use App\Constructor\Facades\CRUI;
use App\Models\OrdersFields;
use Illuminate\Database\Eloquent\Builder;
use LaravelViews\Facades\UI;
use LaravelViews\Views\TableView;
use LaravelViews\Views\Traits\WithAlerts;

class OrdersFieldsTableView extends TableView
{
    use WithAlerts;

    protected $num = 1;
    protected $model = OrdersFields::class;
    public $cabinet_id = null;

    public $itemComponent = 'components.orders-field';

    protected $paginate = 2000;

    protected $listeners = ['add-field' => 'addField'];

    public function addField($params) {
        $id = $params['id'];

        $cabinet = \App\Models\Cabinet::where('id', $id)->first();
        $of = new \App\Models\OrdersFields();
        $of->cabinet_id = $id;
        $of->name = '-';
        $of->type = 'custom';
        $of->is_first = 0;
        $of->is_accept = 0;
        $of->is_2hours = 0;
        $of->is_30minutes = 0;
        $of->is_require = 0;
        $of->is_visible = 1;
        $of->class = 'w-full';
        $of->is_label = 0;
        $of->save();

        $this->success('Добавлено новое поле');
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
            'Тип',
            'Рассылка',
            'Принял',
            '2 часа',
            '30 мин',
            'Обяз',//'Обязателен',
            //'Виден',
            'Класс',
            'Метка',
            'Подсказка'
        ];

        return $headers;
    }

    public function row(OrdersFields $fields)
    {
        $row = [
            //($this->page -  1) * $this->paginate + $this->num++,
            //UI::editable($fields, 'sort'),
            UI::editable($fields, 'name'),
            UI::editable($fields, 'type'),
            CRUI::check($fields, 'is_first'),
            CRUI::check($fields, 'is_accept'),
            CRUI::check($fields, 'is_2hours'),
            CRUI::check($fields, 'is_30minutes'),
            CRUI::check($fields, 'is_require'),
            //$fields->is_visible,
            UI::editable($fields, 'class'),
            CRUI::check($fields, 'is_label'),
            UI::editable($fields, 'placeholder'),
            /*
            $fields->name,
            $fields->type,
            $fields->is_first,
            $fields->is_accept,
            $fields->is_2hours,
            $fields->is_30minutes,
            $fields->is_require,
            $fields->is_visible,
            $fields->class,
            $fields->is_label,
            $fields->placeholder
             */
        ];
        return $row;
    }

    /**
     * Method fired by the `editable` component, it
     * gets the model instance and a key-value array
     * with the modified dadta
     */
    public function update(OrdersFields $fields, $data)
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
        $r = OrdersFields::query()
            ->where('cabinet_id', $this->cabinet_id)
            ->orderBy('sort');

        return $r;
    }

    /** For actions by item */
    protected function actionsByRow()
    {
        return [
            new DeleteOrderFieldAction(),
            new DownOrderFieldAction(),
            new UpOrderFieldAction(),
        ];
    }

}
