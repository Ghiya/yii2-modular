<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\core;


use modular\core\helpers\ArrayHelper;
use modular\core\models\PackageInit;
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
     * @return bool
     */
    final public static function isPanel()
    {
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
     * Добавляет в приложение модуль веб-ресурса с указанными параметрами инициализации.
     *
     * @param PackageInit $packageInit
     *
     * @return null|\yii\base\Module|Module
     * @throws \yii\base\InvalidConfigException
     */
    public function addPackage(PackageInit $packageInit, $packagePrefix = "")
    {
        \Yii::debug("Initializing resource `$packageInit->title`.", __METHOD__);
        // set and configure package module
        \Yii::$app->setModule(
            $packagePrefix . $packageInit->getModuleId(),
            ArrayHelper::merge(
                file_exists($packageInit->getPath() . '/config/config.php') ?
                    ArrayHelper::merge(
                        require $packageInit->getPath() . '/config/config.php',
                        $packageInit->getLocalParams()
                    ) :
                    $packageInit->getLocalParams(),
                $packageInit->toArray()
            )
        );
        $module = \Yii::$app->getModule($packagePrefix . $packageInit->getModuleId());
        // define application language
        if (isset($module->params['defaults']['language'])) {
            $this->language = $module->params['defaults']['language'];
        }
        // init routing
        if ($module->has('urlManager')) {
            \Yii::$app->getUrlManager()->addRules($module->get('urlManager')->rules);
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
        return $module;
    }

}