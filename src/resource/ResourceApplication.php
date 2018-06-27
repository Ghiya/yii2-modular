<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\resource;


use modular\core\Application;
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
     *
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\ServerErrorHttpException
     */
    final public function bootstrap()
    {
        // parent bootstrapping always goes first because of the modules installing as extensions
        parent::bootstrap();
        // add resource module to the application
        $package = $this->addPackage(PackageInit::getParams());
        $this->name = $package->title;
        // set default routing
        $this->getUrlManager()->addRules(
            [
                '/' =>
                    !empty($package->defaultRoute) ?
                        $package->id . '/' . $package->defaultRoute :
                        $package->id . '/default/index',
            ]
        );
        // configure user component
        if ($package->has('user')) {
            $this->set('user', $package->get('user'));
        }
        // configure error handler component
        if ( !empty($package->params['errorHandler']) ) {
            \Yii::configure($this->get('errorHandler'), $package->params['errorHandler']);
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

}