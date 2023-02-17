<?php

namespace system\modules\categories\controllers;

use system\core\Controller;
use system\core\View;
use system\core\Modules;

class CategoriesAdminController extends Controller {

    public $vars=[];
    public static $html_actions = [
        'default',
    ];

    public function __construct($route) {
        parent::__construct($route);

        $this->model=$this->loadModel('CategoryAdmin');

        $this->vars=[
            'cats' => $this->model->list(),
        ];

        $this->actions = array_merge($this->actions, [
            'dlt',
            'add',
            'save',
            'saveSlug'
        ]);
    }

    public function loadModel($name) {
        $path = 'system\modules\categories\models\\'.ucfirst($name);
        if (class_exists($path)) {
            return new $path;
        }
    }

    public function defaultAction() {
        $vars = [
            'module' => 'categories',
            'section_tpl' => 'admin/category',
        ];
        return $this->view->renderBlock($vars);
    }

    public function listAction() {
        return $this->vars['cats'];
    }

    public function getAction() {
        return $this->model->gets($_POST);
    }

    public function moveAction() {
        $action = $_GET['action'];
        switch ($action) {
            case 'move_category':
                $result = $this->model->moveCategory( $_GET);
                break;
        }

        // Возвращаем клиенту успешный ответ
        return [
            'code' => 'success',
            'result' => $result
        ];
    }

    public function default_action($name, $params = []) {
        return $this->model->$name($_POST);
    }


}