<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\core;


use modular\core\events\AfterPackageInitEvent;
use modular\core\helpers\ArrayHelper;
use modular\core\models\PackageInit;
use yii\base\ActionEvent;
use yii\log\FileTarget;


/**
 * Class Application
 * Абстрактный базовый класс приложений модулей ресурсов и панелей их администрирования.
 *
 * @property Module[]|\yii\base\Module[] $modules
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
     * Идентификатор события выполняемого после инициализации пакета ресурса.
     */
    const EVENT_AFTER_PACKAGE_INIT = 'crm.application.afterPackageInit';


    /**
     * @var string
     */
    public $l12nDefault = 'ru-RU';


    /**
     * @var string
     */
    public $l12nParam = 'localize';


    /**
     * @var string
     */
    protected $packagePrefix = "";


    /**
     * {@inheritdoc}
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function bootstrap()
    {
        // parent bootstrapping always goes first because of the modules installing as extensions
        parent::bootstrap();
        $this->on(
            self::EVENT_AFTER_PACKAGE_INIT,
            function ($event) {
                $this->afterPackageInit($event);
            }
        );
        foreach ($this->getPackagesParams() as $packageParams) {
            $this->addPackage($packageParams);
        }
        // the very first EVENT_BEFORE_ACTION
        $this->on(
            self::EVENT_BEFORE_ACTION,
            function ($event) {
                $this->prependedBeforeAction($event);
            },
            [],
            false
        );
    }


    /**
     * @param AfterPackageInitEvent $event
     */
    protected function afterPackageInit($event)
    {
    }


    /**
     * Действия выполняющиеся в самом начале `EVENT_BEFORE_ACTION`, раньше всех остальных.
     *
     * @param ActionEvent $event
     */
    protected function prependedBeforeAction($event)
    {
    }


    /**
     * Возвращает список параметров пакетов для инициализации.
     *
     * @return array
     */
    abstract protected function getPackagesParams();


    /**
     * @return bool
     */
    final public static function isPanel()
    {
        return \Yii::$app->id == self::PANEL_APP_ID;
    }


    /**
     * @param PackageInit $packageInit
     * @param string      $packagePrefix
     * @param bool        $initOnly
     *
     * @throws \yii\base\InvalidConfigException
     * @todo удалить $packagePrefix, $initOnly как избыточные
     *
     * Добавляет в приложение модуль веб-ресурса с указанными параметрами инициализации.
     *
     */
    public function addPackage(PackageInit $packageInit, $packagePrefix = "", $initOnly = false)
    {
        \Yii::debug("Initializing resource: `$packageInit->title`.", __METHOD__);
        // set and configure package module
        \Yii::$app->setModule(
            $this->packagePrefix . $packageInit->getModuleId(),
            $packageInit->getModuleParams()
        );
        $module = \Yii::$app->getModule($this->packagePrefix . $packageInit->getModuleId());
        // init routing
        if ($module->has('urlManager')) {
            \Yii::$app->getUrlManager()->addRules($module->get('urlManager')->rules);
        }
        // define application language
        if (isset($module->params['defaults']['language'])) {
            $this->language = $module->params['defaults']['language'];
        }
        // define translations
        if ($module->has('i18n')) {
            $this->i18n->translations =
                ArrayHelper::merge(
                    $this->i18n->translations,
                    $module->get('i18n')->translations
                );
        }
        if (!self::isPanel()) {
            // define module logs
            $this->log->targets[] = new FileTarget($packageInit->getLogParams());
        }
        $this->trigger(
            self::EVENT_AFTER_PACKAGE_INIT,
            new AfterPackageInitEvent([
                'module'      => $module,
                'config'      => $packageInit->getModuleParams(),
                'packageInit' => $packageInit
            ])
        );
    }


    /**
     * @return string
     */
    public function getPackagePrefix()
    {
        return $this->packagePrefix;
    }


    /**
     * Устанавливает локализацию приложения в зависимости от значения параметра, если он есть в запросе.
     * Название параметра запроса устнавливается свойством `$l12nParam` и по-умолчанию равно `localize`.
     * Если сессионное значение отсутствует, то устанавливается значение свойства `$l12nDefault`? по-умолчанию `ru-RU`.
     */
    protected function setLanguageWithSession()
    {
        $this->language =
            \Yii::$app->request->get(
                $this->l12nParam,
                $this->getSession()->has($this->l12nParam) ?
                    $this->getSession()->get($this->l12nParam) : $this->l12nDefault
            );
        $this->getSession()->set($this->l12nParam, $this->language);
    }


}