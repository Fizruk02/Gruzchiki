<?php

namespace system\modules\products\controllers;

use system\core\Controller;
use system\core\View;
use system\core\Modules;

class ProductsAdminController extends Controller {

    public $vars=[];
    public static $html_actions = [
        'default',
        'item',
    ];

    public function __construct($route) {

        parent::__construct($route, false);

        $this->model=$this->loadModel('ProductsAdmin');

        $this->vars=[
            //'cats' => $this->model->getList(),
        ];

        $this->actions = array_merge($this->actions, [
            //'item',
            'remove',
            'save',
            'getData',

            'listEditPriority',
            'listEditName',
            'listEditPrice',
            'listEditFiles',

            'getListDir',
            'editDir',
            'removeDir',
            'addDir',
            'saveSlug'
        ]);
    }

    public function loadModel($name) {
        $path = 'system\modules\products\models\\'.ucfirst($name);

        if (class_exists($path)) {
            return new $path;
        }
    }

    public function defaultAction() {
        if (isset($_GET['itemId'])) return $this->itemAction();
        $vars = [
            'module' => 'products',
            'section_tpl' => 'admin/product_admin',
        ];
        return $this->view->renderBlock($vars);
    }

    public function listAction() {
        return $this->model->getList($_POST);
    }

    public function itemAction() {
        $input = [
            'id_item' => $_GET['itemId'] ?? null,
        ];
        $this->vars = [
            'module' => 'products',
            'section_tpl' => 'admin/item',
            'data' => $this->model->getData($input),
        ];
        return $this->view->renderBlock($this->vars);
        //return $this->view->render($info);
    }

    public function default_action($name, $params = []) {
        return $this->model->$name($_POST);
    }


}