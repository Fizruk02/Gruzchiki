<?php
/**
 * @author Alexey Garkin wmstr2007@yandex.ru
 * @copyright Copyright (c) 2022 b2bot.ru
 */

namespace App\Models;

class Module {
    /**
     * @var string Уникальный ID идентифитатор модуля
     */
    public $id;

    /**
     * @var Object Страница
     */
    protected $page = null;

    /**
     * @var Object Секция
     */
    protected $section = null;

    /**
     * @var array Список контроллеров
     */
    protected $_controllers = [];

    /**
     * @var string Экшен по умолчанию
     */
    public $defaultRoute = 'default';

    /**
     * @var array Массив модулей от которых зависит данный модуль
     */
    protected $_modules = [];

    /**
     * @var array Массив настроек
     */
    protected $_properties = [];

    /**
     * Конструктор
     * @param $config array Массив конфигурации модуля
     */
    public function __construct($config = []) {
        if (is_array($config) && !empty($config)) {
            foreach ($config as $key => $value) {
                $this->$key = $value;
            }
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

    /**
     * Настройки модуля
     * @return array Массив настроек модуля
     */
    public function getProperties() {
        return $this->_properties;
    }

    /*
     * Возвращает строку со значением или строку объедененого массива через запятую
     *
     * @param mixed $value Значение переменной
     * @param $index Индекс массива если нужен
     */
    public static function getStringValue($value, $index = null, $default = '', $subst = ',') {
        if ($index) {
            if (!isset($value[$index])) return $default;
            $value = $value[$index];
        }
        if ($value === null || (is_array($value) && empty($value))) return $default;
        if (is_array($value)) return implode($subst, $value);
        return $value;
    }

    // Переводы
    public static function translate($block, &$page) {
        //$model = new \App\Models\WebText();
        $trans = \App\Models\WebText::select(['node', 'text'])
            //->join('web_translate', 'web_text.id', '=', 'web_translate.tr_id')
            ->join('web_translate', function ($join) {
                $join->on('web_text.id', '=', 'web_translate.tr_id')
                    ->where('web_translate.lan', '=', 'ru');
            })
            ->where('page_id', '=', $page->id)
            ->orWhere('layout_id', '=', $page->layout_id)
            ->get();

        $keys = [];
        $values = [];
        if (!empty($trans)) foreach ($trans as $t) {
            $keys[] = $t['node'];
            $values[] = $t['text'];
        }

        $cmtBlocks = [];
        $cmtBlocksValues = [];
        $cmtBlocksTr = [];

        //<!--T-->Привет<!--/T-->
        preg_match_all('#<!--T-->(.*?)<!--\/T-->#is', $block, $matches);
        foreach ($matches[0] as $value) {
            $code = preg_replace('#<!--T-->#is', '', $value);
            $code = preg_replace('#<!--\/T-->#is', '', $code);
            $cmtBlocksValues[] = $code;
            $cmtBlocks[$code] = $value;
        }

        $trs = \App\Models\WebText::select(['node', 'text'])
            ->join('web_translate', 'web_text.id', '=', 'web_translate.tr_id')
            ->where('page_id', 'IS NULL')
            ->where('layout_id', 'IS NULL')
            ->where('section_id', 'IS NULL')
            ->where('lan', '=', 'ru')
            ->whereIn('node', [\App\Models\Module::getStringValue($cmtBlocksValues)])
            ->get();

        foreach ($trs as $tr) {
            $keys[] = $cmtBlocks[$tr['node']];
            $values[] = $tr['text'];
        }

        return str_replace($keys, $values, $block);
    }
}
