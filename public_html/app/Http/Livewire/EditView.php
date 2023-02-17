<?php

namespace App\Http\Livewire;

use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Arr;
use LaravelViews\Facades\UI;
use LaravelViews\Views\Traits\WithActions;
use LaravelViews\Views\View;

class EditView extends View
{
    use WithActions;
    use WithoutModelEvents;

    protected $view = 'detail-view.edit-view';
    protected $modelClass;
    protected $detailComponent = null;

    public $title = '';
    public $subtitle = '';
    public $stripe = false;

    public $model;
    public $return_url;

    public function mount()
    {
        $this->setModel();
        $this->setHeading();
    }

    protected function getRenderData()
    {
        $detailData = app()->call([$this, 'edit'], [
            'model' => $this->model,
            'params' => [
                'return_url' => $this->return_url
            ]
        ]);

        if (is_array($detailData)) {
            // If there is an array of data insted of a component
            // then creates a new attributes component
            if (Arr::isAssoc($detailData)) {
                if ($this->detailComponent) {
                    $components = [UI::component($this->detailComponent, $detailData)];
                } else {
                    $components = [UI::attributes($detailData, ['stripe' => $this->stripe])];
                }
            } else {
                $components = $detailData;
            }
        // If there is only one component
        } else {
            $components = [$detailData];
        }

        return [
            'components' => $components,
        ];
    }

    private function setModel()
    {
        if (is_numeric($this->model)) {
            if (!$this->modelClass) {
                throw new Exception('A $modelClass should be declared when the initial model value is an ID');
            }

            $this->model = $this->modelClass::find($this->model);
        }
    }

    private function setHeading()
    {
        if (method_exists($this, 'heading')) {
            $heading = app()->call([$this, 'heading'], ['model' => $this->model]);
            [$this->title, $this->subtitle] = $heading;
        }

        if (!$this->title) {
            $this->title = $this->getClassName();
        }
    }

    public function getActions()
    {
        if (method_exists($this, 'actions')) {
            return $this->actions();
        }

        return [];
    }

    public function getModelWhoFiredAction()
    {
        return $this->model;
    }
}
