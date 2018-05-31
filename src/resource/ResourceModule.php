<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\resource;

use modular\core\Module;


/**
 * Class Module
 * Базовый класс модуля веб-ресурса.
 *
 * @property-read string $index идентификатор абонента в зависимости от выполняемого веб-ресурса
 *
 * @package modular\resource
 */
abstract class ResourceModule extends Module
{


    /**
     * @var bool $indexPrevent если требуется пропуск индексации действия контроллера
     */
    public $indexPrevent = false;


    /**
     * @var array параметры трекера веб-ресурса
     */
    public $tracking = [];


    /**
     * Возвращает идентификатор абонента.
     *
     * @return null|string
     */
    public function getIndex()
    {
        return null;
    }


    /**
     * Возвращает массив описаний действий и контроллеров модуля.
     *
     * @return array
     */
    public static function indexActions()
    {
        return [];
    }


    /**
     * Возвращает описание действия абонента.
     *
     * @param string $controllerId
     * @param string $actionId
     *
     * @return string
     */
    public function indexDescription($controllerId = '', $actionId = '')
    {
        $indexActions = static::indexActions();
        if (in_array($controllerId, array_keys($indexActions))) {
            if (in_array($actionId, array_keys($indexActions[$controllerId]))) {
                return $indexActions[$controllerId][$actionId];
            }
        }
        return 'Не определено';
    }


    /**
     * Возвращает данные просмотра в панели администрирования индексированного действия.
     *
     * @return string в JSON формате
     */
    public function getIndexPanelLink()
    {
        return null;
    }


    /**
     * Если требуется сохранять активность действия.
     * @return bool
     */
    public function shouldIndexAction()
    {
        $indexActions = static::indexActions();
        if (in_array(\Yii::$app->controller->id, array_keys($indexActions))) {
            return !$this->indexPrevent && in_array(\Yii::$app->controller->action->id,
                    array_keys($indexActions[\Yii::$app->controller->id]));
        }
        return false;
    }

}