<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\resource;


use modular\common\Application;
use modular\common\models\ModuleInit;
use modular\resource\models\ActionsIndex;
use modular\resource\modules\Module;


/**
 * Class ResourceApplication
 * Приложение модулей ресурсов.
 *
 * @property-read Module $resourceModule активный модуль веб-ресурса
 *
 * @package modular\resource
 */
class ResourceApplication extends Application
{


    /**
     * @var Module
     */
    private $_module;


    /**
     * {@inheritdoc}
     *
     * @throws \yii\base\ErrorException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\HttpException
     * @throws \yii\web\NotFoundHttpException
     */
    final public function bootstrap()
    {
        // parent bootstrapping always goes first because of the modules installing as extensions
        parent::bootstrap();
        // add resource module to the application
        $this->registerModule(ModuleInit::findResourceByUrl());
        // [[\yii\web\Application::EVENT_AFTER_ACTION]]
        $this->on(self::EVENT_AFTER_ACTION, function ($event) {
            /** @var Module $module */
            $module = $event->sender->controller->module;
            // save resource activity if possible
            if ($module->hasMethod('shouldIndexAction') && $module->shouldIndexAction()) {
                ActionsIndex::add($module);
            }
        });
    }


    /**
     * {@inheritdoc}
     */
    final public function registerModule(ModuleInit $init)
    {
        parent::registerModule($init);
        /** @var Module _module */
        $this->_module = $this->getModule($init->moduleId);
        $this->name = $init->title;
        // set default routing
        $this->getUrlManager()->addRules(['/' => !empty($init->version) ? $init->version : $init->uniqueId]);
        // configure tracking component
        \Yii::configure(\Yii::$app->get('tracking'), $this->_module->tracking);
        // configure user component
        if ($this->_module->has('user')) {
            $this->set('user', $this->_module->get('user'));
        }
        // configure error handler component
        if (!empty($this->_module->params['errorHandler'])) {
            foreach ($this->_module->params['errorHandler'] as $param => $value) {
                $this->errorHandler->{$param} = $value;
            }
        }
    }


    /**
     * Возвращает модуль активного веб-ресурса.
     *
     * @return Module
     */
    final public function getResource()
    {
        return $this->_module;
    }

}