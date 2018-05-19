<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\resource;


use common\Application;
use modular\common\models\ModuleInit;
use resource\models\ActionsIndex;
use yii\helpers\ArrayHelper;


/**
 * Class ResourceApplication
 * Приложение модулей веб-ресурсов системы управления.
 *
 * @package modular\resource
 */
final class ResourceApplication extends Application
{


    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'resource\behaviors\SubscriberContext',
            ]
        );
    }


    /**
     * @inheritdoc
     */
    public function bootstrap()
    {
        // регистрирует модуль ресурса в приложении
        $this->registerModule(ModuleInit::findResourceByUrl());

        // для события окончания действия [[\yii\web\Application::EVENT_AFTER_ACTION]]
        $this->on(self::EVENT_AFTER_ACTION, function ($event) {

            // отправляет все уведомления ресурса
            if (\Yii::$app->controller->module->has('tracker')) {
                \Yii::$app->controller->module->get('tracker')->sendNotices();
            }
            // сохраняет активность модулей веб-ресурсов там где это возможно
            /** @var \modular\resource\modules\Module $module */
            $module = $event->sender->controller->module;
            if ($module->hasMethod('shouldIndexAction') && $module->shouldIndexAction()) {
                ActionsIndex::add($module);
            }
        });
        parent::bootstrap();
    }


    /**
     * @inheritdoc
     */
    public function registerModule(ModuleInit $moduleInit)
    {
        parent::registerModule($moduleInit);

        $this->name = $moduleInit->title;

        // устанавливаем в приложение правило роутинга ресурса по-умолчанию
        $this->defaultRoute = $moduleInit->defaultRoute;

        // устанавливаем компонент пользователя
        if ($moduleInit->resource->has('user')) {
            $this->set('user', $moduleInit->resource->get('user'));
        }

        // устанавливаем компонент обработчика ошибок
        if (!empty($moduleInit->resource->params['errorHandler'])) {
            foreach ($moduleInit->resource->params['errorHandler'] as $param => $value) {
                $this->errorHandler->{$param} = $value;
            }
        }

        // устанавливает параметры ресурса в параметры приложения если они есть
        if (!empty($moduleInit->resource->params)) {
            $this->params = ArrayHelper::merge($this->params, $moduleInit->resource->params);
        }
    }

}