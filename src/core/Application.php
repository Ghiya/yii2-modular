<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\core;


use modular\core\models\ModuleInit;
use yii\base\ErrorException;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;


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
     * @const string PANEL_APP_ID идентификатор приложения панелей администрирования модулей
     */
    const PANEL_APP_ID = 'panel';


    /**
     * @const string RESOURCE_APP_ID идентификатор приложения модулей веб-ресурсов
     */
    const RESOURCE_APP_ID = 'resource';


    /**
     * @const string SERVICES_ID идентификатор расположения системных модулей
     */
    const SERVICES_ID = 'services';


    /**
     * @const string SERVICE_INTERFACE
     */
    const SERVICE_INTERFACE = 'common\services\ServicesInterface';


    /**
     * @const string SERVICE_BILLING_ID
     */
    const SERVICE_BILLING_ID = '_billing';


    /**
     * @const string SERVICE_SMSC_ID
     */
    const SERVICE_SMSC_ID = '_smsc';


    /**
     * @const string SERVICE_PROVIDER_ID
     */
    const SERVICE_PROVIDER_ID = '_provider';


    /**
     * {@inheritdoc}
     */
    final public function __construct($config = [])
    {
        parent::__construct(
            ArrayHelper::merge(
                ArrayHelper::merge(
                    require(\Yii::getAlias('@common/config/main.php')),
                    require(\Yii::getAlias('@common/config/main-local.php'))
                ),
                (array)$config
            )
        );
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
     * Регистрирует в приложении модуль ресурса с указанными параметрами.
     *
     * @param ModuleInit $init модель параметров модуля
     *
     * @throws ErrorException
     * @throws HttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function registerModule(ModuleInit $init)
    {
        \Yii::debug("Регистрация модуля ресурса `$init->title`.", __METHOD__);
        // init module
        $defaultModuleClass =
            $this->isBackend ?
                '@modular\panel\modules\Module' :
                '@modular\resource\modules\Module';
        $this->setModule(
            $init->moduleId,
            [
                'class'        =>
                    file_exists($init->resourceAlias . '/Module.php') ?
                        $init->resourcePath . '\Module' :
                        $defaultModuleClass,
                'title'        => $init->title,
                'description'  => $init->description,
                'isProvider'   => $init->isProvider,
                'isService'    => $init->isService,
                'isResource'   => $init->isResource,
                'bundleParams' => $init->toArray()
            ]
        );
        // configure module
        if (file_exists($init->resourceAlias . '/config/config.php')) {
            $this->config(
                $init->moduleId,
                $init->resourceAlias
            );
        }
        else {
            throw new HttpException(
                500,
                'Отсутствует конфигурационный файл модуля с идентификатором `' . $init->moduleId . '`'
            );
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
    protected function config($moduleId, $modulePath)
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
    }


    /**
     * Возвращает read-only массив всех модулей ресурсов провайдеров данных внешних сервисов.
     *
     * @return Module[]
     */
    public function getProviders()
    {
        $resources = [];
        foreach ($this->modules as $module) {
            if (is_object($module) && isset($module->bundleParams) && $module->bundleParams['type'] == ModuleInit::TYPE_PROVIDER) {
                $resources[] = $module;
            }
        }
        return $resources;
    }


    /**
     * Возвращает read-only массив всех модулей служебных системных ресурсов.
     *
     * @return Module[]
     */
    public function getServices()
    {
        $resources = [];
        foreach ($this->modules as $module) {
            if (is_object($module) && isset($module->bundleParams) && $module->bundleParams['type'] == ModuleInit::TYPE_SERVICE) {
                $resources[] = $module;
            }
        }
        return $resources;
    }


    /**
     * Возвращает read-only массив модулей всех панелей администрирования веб-ресурсов.
     *
     * @return Module[]
     */
    public function getPanels()
    {
        $resources = [];
        foreach ($this->modules as $module) {
            if (is_object($module) && isset($module->bundleParams) && $module->bundleParams['type'] == ModuleInit::TYPE_RESOURCE) {
                $resources[] = $module;
            }
        }
        return $resources;
    }


    /**
     * Возвращает read-only массив всех моделей параметров модулей веб-ресурсов системы.
     * Возвращает только активные модули.
     *
     * @return ModuleInit[]
     */
    public function getResourceBundles()
    {
        return ModuleInit::findResources([self::RESOURCE_APP_ID], true);
    }


    /**
     * Если выполняется приложение административных панелей ресурсов.
     *
     * @return bool
     */
    final public function getIsBackend()
    {
        return ($this->id === self::PANEL_APP_ID);
    }

}