<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\resource;


use modular\core\Application;
use modular\core\models\ModuleInit;
use modular\resource\models\ActionsIndex;
use yii\log\FileTarget;


/**
 * Class ResourceApplication
 * Приложение модулей ресурсов.
 *
 * @property-read ResourceModule $resourceModule активный модуль веб-ресурса
 *
 * @package modular\resource
 */
class ResourceApplication extends Application
{


    /**
     * @var ResourceModule
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
            /** @var ResourceModule $module */
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
        /** @var ResourceModule _module */
        $this->_module = $this->getModule($init->moduleId);
        $this->name = $init->title;
        // define module logs
        $this->log->targets[] = new FileTarget($init->getLogParams());
        // set default routing
        $this->getUrlManager()->addRules(
            [
                '/' =>
                    !empty($this->_module->defaultRoute) ?
                        $init->routeId . '/' . $this->_module->defaultRoute :
                        $init->routeId . '/default/index',
            ]
        );
        // configure user component
        if ($this->_module->has('user')) {
            $this->set('user', $this->_module->get('user'));
        }
        // configure error handler component
        \Yii::configure($this->get('errorHandler'), $this->_module->errorsConfig);
        if (!empty($this->_module->params['errorHandler'])) {
            foreach ($this->_module->params['errorHandler'] as $param => $value) {
                $this->errorHandler->{$param} = $value;
            }
        }
    }


    /**
     * Возвращает модуль активного веб-ресурса.
     *
     * @return ResourceModule
     */
    final public function getResource()
    {
        return $this->_module;
    }

}