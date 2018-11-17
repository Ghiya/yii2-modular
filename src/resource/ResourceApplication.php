<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\resource;


use modular\core\Application;
use modular\core\events\AfterPackageInitEvent;
use modular\core\models\PackageInit;


/**
 * Class ResourceApplication
 * Приложение модулей веб-ресурсов.
 *
 * @package modular\resource
 */
class ResourceApplication extends Application
{


    /**
     * {@inheritdoc}
     */
    final public function init()
    {
        parent::init();
        $this->on(
            self::EVENT_AFTER_PACKAGE_INIT,
            function (AfterPackageInitEvent $event) {
                $this->name = $event->module->title;
                // set default routing
                $this->getUrlManager()->addRules(
                    [
                        '/' =>
                            !empty($event->module->defaultRoute) ?
                                $event->module->id . '/' . $event->module->defaultRoute :
                                $event->module->id . '/default/index',
                    ]
                );
                // configure error handler component
                if (!empty($event->module->params['errorHandler'])) {
                    \Yii::configure($this->get('errorHandler'), $event->module->params['errorHandler']);
                }
            }
        );
    }


    /**
     * {@inheritdoc}
     *
     * @throws \yii\web\ServerErrorHttpException
     */
    protected function getPackagesParams()
    {
        return [PackageInit::getParams()];
    }


}