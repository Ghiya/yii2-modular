<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\common;


use modular\common\models\ModuleInit;
use modular\common\modules\Module;
use yii\base\ErrorException;
use yii\helpers\ArrayHelper;
use yii\log\FileTarget;
use yii\web\HttpException;


/**
 * Class Application
 * Базовый класс приложений модулей системы управления.
 *
 * @property Module[]     $modules
 * @property Module[]     $panels          read-only массив модулей панелей администрирования веб-ресурсов
 * @property Module[]     $providers       read-only массив модулей ресурсов провайдеров данных внешних сервисов
 * @property Module[]     $services        read-only массив модулей служебных системных ресурсов
 * @property ModuleInit[] $resourceBundles read-only массив моделей параметров модулей веб-ресурсов системы
 * @property bool         $isBackend       read-only если выполняется приложение административных панелей
 *
 * @package common
 */
class Application extends \yii\web\Application
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
    public function __construct($config = [])
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
    public function coreComponents()
    {
        return ArrayHelper::merge(
            parent::coreComponents(),
            [
                'authManager' => [
                    'class'          => 'yii\rbac\PhpManager',
                    'assignmentFile' => '@common/rbac/assignments.php',
                    'itemFile'       => '@common/rbac/items.php',
                    'ruleFile'       => '@common/rbac/rules.php',
                ],
                'cache'       => [
                    'class' => 'yii\caching\FileCache',
                ],
                'session'     => [
                    'class' => 'yii\web\Session',
                ],
                'formatter'   => [
                    'dateFormat'        => 'dd.MM.yyyy',
                    'decimalSeparator'  => '.',
                    'thousandSeparator' => ' ',
                    'locale'            => 'ru-RU',
                    'defaultTimeZone'   => 'Europe/Moscow',
                    'nullDisplay'       => '<i class="fa fa-minus"></i>',
                ],
            ]
        );
    }


    /**
     * Регистрирует в приложении модуль ресурса с указанными параметрами.
     *
     * @param ModuleInit $moduleInit модель параметров модуля
     *
     * @throws ErrorException
     * @throws HttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function registerModule(ModuleInit $moduleInit)
    {
        if (!empty($moduleInit)) {
            \Yii::debug("Регистрация модуля ресурса `$moduleInit->title`.", __METHOD__);
            // init module
            $defaultModuleClass =
                $this->isBackend ?
                    '@modular\panel\modules\Module' :
                    '@modular\resource\modules\Module';
            $this->setModule($moduleInit->moduleId, [
                'class'       =>
                    file_exists($moduleInit->resourceAlias . '/Module.php') ?
                        $moduleInit->resourcePath . '\Module' :
                        $defaultModuleClass,
                'title'       => $moduleInit->title,
                'description' => $moduleInit->description,
                'isProvider'  => $moduleInit->isProvider,
                'isService'   => $moduleInit->isService,
                'isResource'  => $moduleInit->isResource,
            ]);
            // configure module
            if (file_exists($moduleInit->resourceAlias . '/config/config.php')) {
                $configCommon = require($moduleInit->resourceAlias . '/config/config.php');
                $configLocal = (file_exists($moduleInit->resourceAlias . '/config/config-local.php')) ?
                    require($moduleInit->resourceAlias . '/config/config-local.php') :
                    [];
                \Yii::configure(
                    $this->getModule($moduleInit->moduleId),
                    ArrayHelper::merge(
                        ArrayHelper::merge(
                            $configCommon,
                            [
                                'params' =>
                                    [
                                        'bundleParams' => $moduleInit->toArray(),
                                    ]
                            ]
                        ),
                        $configLocal
                    )
                );
                // define application language
                if (isset($this->getModule($moduleInit->moduleId)->params['defaults']['language'])) {
                    $this->language = $this->getModule($moduleInit->moduleId)->params['defaults']['language'];
                }
                // init module logs
                \Yii::$app->log->targets[] = new FileTarget([
                    'logFile'        => !empty($moduleInit->version) ?
                        '@common/logs/'
                        . $moduleInit->section_id . '/'
                        . $moduleInit->module_id . '/'
                        . $moduleInit->version . '/'
                        . date("Y-m/d/")
                        . date("H")
                        . '.log' :
                        '@common/logs/'
                        . $moduleInit->section_id . '/'
                        . $moduleInit->module_id . '/'
                        . date("Y-m/d/")
                        . date("H")
                        . '.log',
                    'exportInterval' => 1,
                    'levels'         => ['error', 'info', 'trace', 'warning',],
                    'categories'     => [],
                    'prefix'         => function ($message) {
                        return "[" . \Yii::$app->request->userIP . "]";
                    },
                ]);
                // init routing
                if ($this->getModule($moduleInit->moduleId)->has('urlManager')) {
                    \Yii::$app->getUrlManager()->addRules($this->getModule($moduleInit->moduleId)->get('urlManager')->rules);
                }
                // устанавливает компонент языковых локализаций приложения если он есть
                if ($this->getModule($moduleInit->moduleId)->has('i18n')) {
                    $this->i18n->translations = ArrayHelper::merge(
                        $this->i18n->translations,
                        $this->getModule($moduleInit->moduleId)->get('i18n')->translations
                    );
                }
            }
            else {
                throw new HttpException(
                    404,
                    'Отсутствует конфигурационный файл модуля с идентификатором `' . $moduleInit->moduleId . '`'
                );
            }
        }
        else {
            throw new ErrorException('Регистрируемый модуль не определён.');
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
            if (is_object($module) && isset($module->params['bundleParams']) && $module->params['bundleParams']['type'] == ModuleInit::TYPE_PROVIDER) {
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
            if (is_object($module) && isset($module->params['bundleParams']) && $module->params['bundleParams']['type'] == ModuleInit::TYPE_SERVICE) {
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
            if (is_object($module) && isset($module->params['bundleParams']) && $module->params['bundleParams']['type'] == ModuleInit::TYPE_RESOURCE) {
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
    public function getIsBackend()
    {
        return ($this->id === self::PANEL_APP_ID);
    }

}