<?php
/**
 * @copyright Copyright (c) 2017. Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\panelmodules\_default;

use modular\modular\commonDispatcher;
use modular\panelmodels\UserRole;
use yii\helpers\ArrayHelper;


/**
 * Class Module базовый класс модуля панели администрирования системы.
 *
 * @property array $panelItems     массив элементов меню панели администрирования модуля
 * @property array $state          read-only данные статуса соединения провайдера с внешним сервисом
 *
 * @package modular\panelmodules\_default
 * @author  Ghiya Mikadze <ghiya@mikadze.me>
 */
class Module extends \modular\modular\commonmodules\_default\Module
{


    /**
     * @var array $_panelItems
     */
    private $_panelItems = [];


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        // меню администрирования
        if (\Yii::$app->user->can(UserRole::PM_ACCESS_LOGS) && $this->isProvider ) {
            $this->panelItems = [
                [
                    'label'   => '<span class="pull-left"><i class="fa fa-code"></i></span><span class="pull-right">Логи запросов</span>',
                    'url'     => ['/modules.logs/' . $this->id],
                    'encode'  => false,
                    'active'  => (boolean)preg_match("/modules.logs\/" . $this->id . "/i", \Yii::$app->request->url),
                    'options' => ['class' => 'clearfix', 'data' => ['spinner' => 'true']],
                ],
            ];
        }
        if (\Yii::$app->user->can(UserRole::PM_ACCESS_TRACKS) && $this->isResource ) {
            $this->panelItems = [
                [
                    'label'   => '<span class="pull-left"><i class="fa fa-envelope"></i></span><span class="pull-right">Уведомления</span>',
                    'url'     => ['/modules.tracks/' . $this->id],
                    'encode'  => false,
                    'active'  => (boolean)preg_match("/modules.tracks\/" . $this->id . "/i", \Yii::$app->request->url),
                    'options' => ['class' => 'clearfix', 'data' => ['spinner' => 'true']],
                ],
            ];
        }
        if (\Yii::$app->user->can(UserRole::PM_VIEW_DEBUG_DATA) && $this->isResource) {
            $this->panelItems = [
                [
                    'label'   => '<span class="pull-left"><i class="fa fa-eye"></i></span><span class="pull-right">Активность</span>',
                    'url'     => ['/' . $this->id . '/actions'],
                    'encode'  => false,
                    'active'  => (boolean)preg_match("/" . $this->id . "\/actions/i", \Yii::$app->request->url),
                    'options' => ['class' => 'clearfix', 'data' => ['spinner' => 'true']],
                ],
            ];
        }
    }


    /**
     * Возвращает пункты меню панели администрирования модуля ресурса системы.
     * @return array
     */
    public function getPanelItems()
    {
        return $this->_panelItems;
    }


    /**
     * Добавляет пункт меню панели администрирования модуля ресурса системы.
     * Если пункт - первый, то сначала добавлется корневой элемент меню модуля ресурса.
     *
     * @param array $panelItems массив элементов меню согласно [[\yii\bootstrap\Nav::$items]]
     */
    public function setPanelItems($panelItems = [])
    {
        // добавляем корневой пункт меню если требуется
        if (empty($this->_panelItems)) {
            $this->_panelItems = [
                'label'  => $this->title,
                'id'     => $this->safeId,
                'active' => (boolean)preg_match("/" . $this->id . "/i", \Yii::$app->request->url),
                'items'  => [],
            ];
        }
        $panelItems = (ArrayHelper::isIndexed($panelItems)) ? $panelItems : [$panelItems,];
        $this->_panelItems['items'] = ArrayHelper::merge($this->_panelItems['items'], $panelItems);
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
        if (!empty($this->stateData())) {
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
        } else {
            \Yii::error('Отсутствуют данные активности соединения для модуля `' . $this->id . '`', __METHOD__);
            return [];
        }
    }

}