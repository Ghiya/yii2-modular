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
        $resource = $this->initResource(ModuleInit::getItems());
        $this->name = $resource->title;
        // set default routing
        $this->getUrlManager()->addRules(
            [
                '/' =>
                    !empty($resource->defaultRoute) ?
                        $resource->id . '/' . $resource->defaultRoute :
                        $resource->id . '/default/index',
            ]
        );
        // configure user component
        if ($resource->has('user')) {
            $this->set('user', $resource->get('user'));
        }
        // configure error handler component
        \Yii::configure($this->get('errorHandler'), $resource->errorsConfig);
        if (!empty($resource->params['errorHandler'])) {
            foreach ($resource->params['errorHandler'] as $param => $value) {
                $this->errorHandler->{$param} = $value;
            }
        }
        /*$this->on(
            self::EVENT_AFTER_ACTION,
            function ($event) {
                $module = $event->sender->controller->module;
                // save resource activity if possible
                if ($module->hasMethod('shouldIndexAction') && $module->shouldIndexAction()) {
                    ActionsIndex::add($module);
                0}
            }
        );*/
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