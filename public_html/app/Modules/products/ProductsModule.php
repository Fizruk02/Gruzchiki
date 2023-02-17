<?php
/**
 * @author Alexey Garkin wmstr2007@yandex.ru
 * @copyright Copyright (c) 2022 b2bot.ru
 */

namespace system\modules\products;

use system\core\Module;

class ProductsModule extends Module {
    /**
     * @var string Уникальный ID идентифитатор модуля
     */
    public $id = 'products';

    /**
     * @var string Описание модуля
     */
    public $name = 'Модуль управления продуктами';

    /**
     * @var bool Админская часть или пользовательская
     */
    protected $is_admin = false;

    /**
     * @var array Список контроллеров
     */
    protected $_controllers = [
        'ProductsController',
        'ProductsAdminController',
    ];


    /**
     * @var string Экшен по умолчанию
     */
    public $defaultRoute = 'default';

    /**
     * @var string Контроллер по умолчанию
     */
    public $defaultController = 'system\modules\products\controllers\ProductsController';

    /**
     * @var array Массив модулей от которых зависит данный модуль
     */
    protected $_modules = [];

    /**
     * @var array Массив JS модулей от которых зависит данный модуль
     */
    protected $_js_modules = ['jsTree'];

    /**
     * @var array Массив настроек
     */
    protected $_properties = [
        'is_admin' => [
            'title' => 'Админская часть',
            'type' => 'bool',
        ]
    ];

    /**
     * Конструктор
     * @param $config array Массив конфигурации модуля
     */
    public function __construct($config) {
        parent::__construct($config);

        if ($this->is_admin) {
            $this->defaultController = 'system\modules\products\controllers\ProductsAdminController';
        }
    }

    /**
     * Контроллеры модуля
     * @return array Массив контроллеров
     */
    public function getControllers() {
        return $this->_controllers;
    }

    /**
     * Зависимости модуля
     * @return array Массив модулей
     */
    public function getModules() {
        return $this->_modules;
    }
}