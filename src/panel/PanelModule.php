<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\panel;

use modular\core\helpers\ArrayHelper;
use modular\core\Module;
use modular\core\tracker\models\TrackData;


/**
 * Class Module
 * Базовый класс модуля панели администрирования веб-ресурса.
 *
 * @property-read PanelApplication $module
 *
 * @property array                 $panelItems     массив элементов меню панели администрирования модуля
 * @property array                 $state          read-only данные статуса соединения провайдера с внешним сервисом
 *
 * @package modular\panel
 */
abstract class PanelModule extends Module
{


    const NAV_GROUP_RESOURCES = 'resources';


    const NAV_GROUP_SERVICES = 'services';


    const NAV_GROUP_INTERNAL = 'internal';


    /**
     * @var bool индекс для фильтрации отображения в панели администрирования
     */
    public $panelGroup = self::NAV_GROUP_RESOURCES;


    /**
     * @return array
     */
    abstract protected function menuItems();


    /**
     * @return string
     */
    abstract protected function menuPermission();


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        // add navigation items for the authorized user ONLY
        if ($this->accessAllowed()) {
            $this->module->navigation =
                [
                    'id'          => $this->id,
                    'title'       => $this->title,
                    'description' => $this->description,
                    'urls'        => ArrayHelper::renameKeys($this->urls, ['is_active' => 'isActive']),
                    'version'     => $this->version,
                    'active'      => (boolean)preg_match("/\/$this->id/i", \Yii::$app->request->url),
                    'items'       => $this->menuItems(),
                    'tracks'      => TrackData::countActive($this->cid, \Yii::$app->user->id),
                ];
        }
    }


    /**
     * @return bool
     */
    protected function accessAllowed() {
        $permissions = (array)$this->menuPermission();
        if ( !empty($permissions) ) {
            foreach ($permissions as $permission) {
                if ( \Yii::$app->user->can($permission) ) {
                    return true;
                }
            }
            return false;
        }
        return true;
    }


    /**
     * @return string
     */
    public function state()
    {
        return '';
    }


    /**
     * Возвращает статус соединения с сервисом адаптера.
     * @return string
     */
    protected function stateData()
    {
        if (\Yii::$app->session->has('modules.' . $this->id . '.state')) {
            $stateData = explode("__", (string)\Yii::$app->session->get('modules.' . $this->id . '.state'));
            if (count($stateData) == 2) {
                return (string)$stateData[0];
            }
        }
        return '';
    }


    /**
     * Возвращает метку обновления статуса соединения с сервисом адаптера.
     * @return int
     */
    protected function stateTimestamp()
    {
        if (\Yii::$app->session->has('modules.' . $this->id . '.state')) {
            $stateData = explode("__", (string)\Yii::$app->session->get('modules.' . $this->id . '.state'));
            if (count($stateData) == 2) {
                return (int)$stateData[1];
            }
        }
        return 0;
    }


    /**
     * Возвращает read-only данные статуса соединения провайдера с внешним сервисом.
     *
     * @return array
     */
    public function getState()
    {
        if ($this->stateData() !== null) {
            if (time() < $this->stateTimestamp() + \Yii::$app->params['providerStateExpires']) {
                return [
                    $this->stateData(),
                    $this->stateTimestamp(),
                ];
            }
        }
        $stateData = $this->state();
        if (!empty($stateData)) {
            $timestamp = \Yii::$app->formatter->asTimestamp(time());
            \Yii::$app->session->set('modules.' . $this->id . '.state', (string)$stateData . "__" . $timestamp);
            return [
                $stateData,
                $timestamp,
            ];
        }
        else {
            \Yii::error('Отсутствуют данные активности соединения для модуля `' . $this->id . '`', __METHOD__);
            return [];
        }
    }

}