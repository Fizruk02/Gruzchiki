<?php

namespace system\modules\products\controllers;

use system\core\Controller;
use system\core\View;
use system\core\Modules;

class ProductsController extends Controller {

    public $vars = [];
    public static $html_actions = [
        'default',
        'item',
        'slider'
    ];

    public function __construct($route) {
        parent::__construct($route, false);
        $this->model = $this->loadModel('Products');
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
            'section_tpl' => 'product',
            'data' => $this->route['data'],
            'item' => $this->model->item(),
            'files' => $this->model->files(),
            'translates' => $this->model->translates(),
        ];
        return $this->view->renderBlock($vars);
    }

    public function sliderAction() {
        $this->model=$this->loadModel('ProductsSlider');
        $vars = [
            'module' => 'products',
            'section_tpl' => 'slider',
            'data' => $this->route['data'],
            'items' => $this->model->items(),
            'cats' => $this->model->cats(),
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
            'section_tpl' => 'item',
            'data' => $this->model->getData($input),
        ];
        return $this->view->renderBlock($this->vars);
        //return $this->view->render($info);
    }

    public function default_action($name, $params = []) {
        return $this->model->$name($_POST);
    }

    public function getEditView()
    {
        ob_start();
        if ($this->route['name'] == 'products/products/slider') require __DIR__.'/../templates/edit_slider.php';
        else  require __DIR__.'/../templates/edit.php';
        $content = ob_get_clean();
        return $content;
    }
}