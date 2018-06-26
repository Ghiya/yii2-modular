<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\core;


use modular\core\models\ModuleInit;
use modular\panel\PanelModule;
use modular\resource\ResourceModule;
use yii\base\ErrorException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\log\FileTarget;
use yii\web\HttpException;
use yii\web\ServerErrorHttpException;


/**
 * Class Application
 * Абстрактный базовый класс приложений модулей ресурсов и панелей их администрирования.
 *
 * @property Module[]     $modules
 * @property Module[]     $panels          read-only массив модулей панелей администрирования веб-ресурсов
 * @property Module[]     $providers       read-only массив модулей ресурсов провайдеров данных внешних сервисов
 * @property Module[]     $services        read-only массив модулей служебных системных ресурсов
 * @property ModuleInit[] $resourceBundles read-only массив моделей параметров модулей веб-ресурсов системы
 * @property bool         $isBackend       read-only если выполняется приложение административных панелей
 *
 * @package core
 */
abstract class Application extends \yii\web\Application
{


    /**
     * Идентификатор приложения панелей администрирования
     */
    const PANEL_APP_ID = 'panel';


    /**
     * Идентификатор приложения веб-ресурсов
     */
    const RESOURCE_APP_ID = 'resource';



    /**
     * @return bool
     */
    final public static function isPanel() {
        return \Yii::$app->id == self::PANEL_APP_ID;
    }

    /**
     * {@inheritdoc}
     *
     * Добавляет в компоненты ядра приложения компоненты используемые по-умолчанию.
     *
     */
    final public function coreComponents()
    {
        return ArrayHelper::merge(
            parent::coreComponents(),
            [
                'authManager' =>
                    [
                        'class'          => 'yii\rbac\PhpManager',
                        'assignmentFile' => '@common/rbac/assignments.php',
                        'itemFile'       => '@common/rbac/items.php',
                        'ruleFile'       => '@common/rbac/rules.php',
                    ],
                'cache'       =>
                    [
                        'class' => 'yii\caching\FileCache',
                    ],
                'session'     =>
                    [
                        'class' => 'yii\web\Session',
                    ],
                'formatter'   =>
                    [
                        'dateFormat'        => 'dd.MM.yyyy',
                        'decimalSeparator'  => '.',
                        'thousandSeparator' => ' ',
                        'locale'            => 'ru-RU',
                        'defaultTimeZone'   => 'Europe/Moscow',
                        'nullDisplay'       => '<i class="fa fa-minus"></i>',
                    ]
            ]
        );
    }


    /**
     * Инициализирует модуль веб-ресурса относительно указанных параметров.
     *
     * @param ModuleInit $params
     *
     * @return ResourceModule|PanelModule
     * @throws ServerErrorHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function initResource(ModuleInit $params)
    {
        \Yii::debug("Initializing resource `$params->title`.", __METHOD__);
        $resource = $params->appendModule();
        if ( !empty($resource) ) {
            $this->setModule(
                self::isPanel() ? $params->module_id : $params->version,
                $resource
            );
            // configure resource
            \Yii::configure(
                $resource,
                file_exists($params->getPath() . '/config/config-local.php') ?
                    ArrayHelper::merge(
                        require $params->getPath() . '/config/config.php',
                        require $params->getPath() . '/config/config-local.php'
                    ) :
                    require $params->getPath() . '/config/config.php'
            );
            // define application language
            if (isset($resource->params['defaults']['language'])) {
                $this->language = $resource->params['defaults']['language'];
            }
            // init routing
            if ($resource->has('urlManager')) {
                \Yii::$app->getUrlManager()->addRules($resource->get('urlManager')->rules);
            }
            // define translations
            if ($resource->has('i18n')) {
                $this->i18n->translations =
                    ArrayHelper::merge(
                        $this->i18n->translations,
                        $resource->get('i18n')->translations
                    );
            }
            if ( !self::isPanel() ) {
                // define module logs
                $this->log->targets[] = new FileTarget($params->getLogParams());
            }
            return $resource;
        }
        else {
            throw new ServerErrorHttpException("Trying to init undefined resource.");
        }
    }


    /**
     * Конфигурирует модуль и настраивает зависимые параметры приложения.
     *
     * @param string $moduleId
     * @param string $modulePath
     *
     * @throws \yii\base\InvalidConfigException
     */
    /*protected function config($moduleId, $modulePath)
    {
        $withModule = $this->getModule($moduleId);
        \Yii::configure(
            $withModule,
            ArrayHelper::merge(
                require $modulePath . '/config/config.php',
                file_exists($modulePath . '/config/config-local.php') ?
                    require $modulePath . '/config/config-local.php' :
                    []
            )
        );
        // define application language
        if (isset($withModule->params['defaults']['language'])) {
            $this->language = $withModule->params['defaults']['language'];
        }
        // init routing
        if ($withModule->has('urlManager')) {
            \Yii::$app->getUrlManager()->addRules($withModule->get('urlManager')->rules);
        }
        // устанавливает компонент языковых локализаций приложения если он есть
        if ($withModule->has('i18n')) {
            $this->i18n->translations = ArrayHelper::merge(
                $this->i18n->translations,
                $withModule->get('i18n')->translations
            );
        }
    }*/


    /**
     * Возвращает read-only массив всех модулей ресурсов провайдеров данных внешних сервисов.
     *
     * @return Module[]
     */
    /*public function getProviders()
    {
        $resources = [];
        foreach ($this->modules as $module) {
            if (is_object($module) && isset($module->bundleParams) && $module->bundleParams['type'] == ModuleInit::TYPE_PROVIDER) {
                $resources[] = $module;
            }
        }
        return $resources;
    }*/


    /**
     * Возвращает read-only массив всех модулей служебных системных ресурсов.
     *
     * @return Module[]
     */
    /*public function getServices()
    {
        $resources = [];
        foreach ($this->modules as $module) {
            if (is_object($module) && isset($module->bundleParams) && $module->bundleParams['type'] == ModuleInit::TYPE_SERVICE) {
                $resources[] = $module;
            }
        }
        return $resources;
    }*/


    /**
     * Возвращает read-only массив модулей всех панелей администрирования веб-ресурсов.
     *
     * @return Module[]
     */
    /*public function getPanels()
    {
        $resources = [];
        foreach ($this->modules as $module) {
            if (is_object($module) && isset($module->bundleParams) && $module->bundleParams['type'] == ModuleInit::TYPE_RESOURCE) {
                $resources[] = $module;
            }
        }
        return $resources;
    }*/


    /**
     * Возвращает read-only массив всех моделей параметров модулей веб-ресурсов системы.
     * Возвращает только активные модули.
     *
     * @return ModuleInit[]
     */
    /*public function getResourceBundles()
    {
        return ModuleInit::findResources([self::RESOURCE_APP_ID], true);
    }*/


    /**
     * Если выполняется приложение административных панелей ресурсов.
     *
     * @return bool
     */
    /*final public function getIsBackend()
    {
        return ($this->id === self::PANEL_APP_ID);
    }*/

}