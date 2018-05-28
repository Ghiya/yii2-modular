<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\resource;


use modular\common\Application;
use modular\common\models\ModuleInit;
use modular\resource\models\ActionsIndex;
use yii\helpers\ArrayHelper;


/**
 * Class ResourceApplication
 * Приложение модулей ресурсов.
 *
 * @package modular\resource
 */
class ResourceApplication extends Application
{


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
            // sending all resource tracks
            if (\Yii::$app->controller->module->has('tracker')) {
                \Yii::$app->controller->module->get('tracker')->sendNotices();
            }
            // save resource activity if possible
            /** @var \modular\resource\modules\Module $module */
            $module = $event->sender->controller->module;
            if ($module->hasMethod('shouldIndexAction') && $module->shouldIndexAction()) {
                ActionsIndex::add($module);
            }
        });
    }


    /**
     * {@inheritdoc}
     */
    final public function registerModule(ModuleInit $moduleInit)
    {
        parent::registerModule($moduleInit);
        $this->name = $moduleInit->title;
        // set default routing
        $this->getUrlManager()->addRules(['/' => !empty($moduleInit->version) ? $moduleInit->version : $moduleInit->uniqueId]);
        // set user component
        if ($moduleInit->resource->has('user')) {
            $this->set('user', $moduleInit->resource->get('user'));
        }
        // set error handler component's params
        if (!empty($moduleInit->resource->params['errorHandler'])) {
            foreach ($moduleInit->resource->params['errorHandler'] as $param => $value) {
                $this->errorHandler->{$param} = $value;
            }
        }
        // merge application params with resource params
        if (!empty($moduleInit->resource->params)) {
            $this->params = ArrayHelper::merge($this->params, $moduleInit->resource->params);
        }
    }

}