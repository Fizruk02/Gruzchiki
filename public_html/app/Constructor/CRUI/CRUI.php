<?php

namespace App\Constructor\CRUI;

use Illuminate\Support\Facades\View;

class CRUI
{
    public function number($model, $field)
    {
        //$model->{$field} = 1;
        //return $this->component('laravel-views::components.editable', [
        return $this->component('crudbooster::components.number', [
            'model' => $model,
            'field' => $field
        ]);
    }

    public function check($model, $field, $class = null, $title = null)
    {
        return $this->component('crudbooster::components.check', compact(
            'model',
            'field',
            'class',
            'title'
        ));
    }

    public function select($model, $field, $list)
    {
        //$model->{$field} = 1;
        //return $this->component('laravel-views::components.editable', [
        return $this->component('crudbooster::components.select', [
            'model' => $model,
            'field' => $field,
            'list' => $list,
        ]);
    }

    public function badge($title, $type = 'default')
    {
        return $this->component('laravel-views::components.badge', [
            'title' => $title,
            'type' => $type
        ]);
    }

    public function avatar($src)
    {
        return $this->component('laravel-views::components.img', [
            'src' => $src,
            'variant' => 'avatar'
        ]);
    }

    public function link($title, $to)
    {
        return $this->component('laravel-views::components.link', compact(
            'to',
            'title'
        ));
    }

    public function icon($icon, $type = 'default', $class = "")
    {
        return $this->component('laravel-views::components.icon', compact(
            'icon',
            'type',
            'class'
        ));
    }

    public function attributes($attributes, $options = [])
    {
        return $this->component('laravel-views::components.attributes-list', array_merge(
            ['data' => $attributes],
            $options
        ));
    }

    public function component($view, $data = [])
    {
        return View::make('laravel-views::core.dynamic-component')
            ->with([
                'view' => $view,
                'data' => $data,
            ])
            ->render();
    }
}
