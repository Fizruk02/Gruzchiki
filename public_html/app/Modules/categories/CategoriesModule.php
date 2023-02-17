<?php
/**
 * @author Alexey Garkin wmstr2007@yandex.ru
 * @copyright Copyright (c) 2022 b2bot.ru
 */

namespace App\Modules\categories;

use App\Models\Module;

class CategoriesModule extends Module {
    /**
     * @var string Уникальный ID идентифитатор модуля
     */
    public $id = 'categories';

    /**
     * @var string Описание модуля
     */
    public $name = 'Категории';

    /**
     * @var string Описание методов модуля
     */
    public $name_methods = [
        'categories/default' => 'Вывод категорий'
    ];

    /**
     * @var bool Админская часть или пользовательская
     */
    protected $is_admin = false;

    /**
     * @var array Список контроллеров
     */
    protected $_controllers = [
        'CategoriesController',
        'CategoryController',
        'CategoriesAdminController',
    ];


    /**
     * @var string Экшен по умолчанию
     */
    public $defaultRoute = 'default';

    /**
     * @var string Контроллер по умолчанию
     */
    public $defaultController = 'CategoriesController';

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
            $this->defaultController = 'CategoriesAdminController';
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
