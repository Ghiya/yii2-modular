<?php
/**
 * @copyright Copyright (c) 2017. Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace common;


use common\modules\_default\Module;
use common\services\billing\interfaces\BillingInterface;
use common\services\billing\Subscriber;
use common\services\smsc\interfaces\SmscInterface;
use resource\components\Tracker;
use yii\base\ErrorException;
use yii\web\IdentityInterface;

/**
 * Class Dispatcher singleton для доступа ресурсов к постоянно используемым системным объектам.
 *
 * @package common
 * @author  Ghiya Mikadze <ghiya@mikadze.me>
 */
final class Dispatcher
{


    /**
     * @var static $_instance
     */
    private static $_instance;


    /**
     * @var bool $_billingExists
     */
    private static $_billingExists = false;


    /**
     * @var bool $_smscExists
     */
    private static $_smscExists = false;


    /**
     * Возвращает идентификационную модель абонента.
     * > Note: Функционал доступен только в приложении веб-ресурсов, в любом другом вернёт `null`.
     *
     * @return null|IdentityInterface|Subscriber
     *
     */
    public static function subscriber()
    {
        return self::hasSubscriber() ?
            \Yii::$app->user->identity :
            null;
    }


    /**
     * Если абонент определён ( идентификация через стандартный механизм авторизации пользователя ).
     * > Note: Функционал доступен только в приложении веб-ресурсов, в любом другом вернёт `false`.
     *
     * @return bool
     */
    public static function hasSubscriber()
    {
        return !self::app()->isBackend && !\Yii::$app->user->isGuest;
    }


    /**
     * Возвращает объект выполняемого приложения.
     *
     * @return \yii\console\Application|\yii\web\Application|\panel\Application|\resource\Application
     */
    public static function app()
    {
        return \Yii::$app;
    }


    /**
     * Возвращает компонент трекинга уведомлений веб-ресурсов.
     *
     * @return null|object|Tracker
     *
     * @throws \yii\base\InvalidConfigException
     */
    public static function tracker()
    {
        return
            \Yii::$app->controller->module->has('tracker') ?
                \Yii::$app->controller->module->get('tracker') : null;
    }


    /**
     * Возвращает компонент службы взаимодействия с данными биллинговой системы.
     *
     * @return null|object|Module
     *
     * @throws ErrorException
     * @throws \yii\base\InvalidConfigException
     */
    public static function billing()
    {
        if (self::_billingExists()) {
            return \Yii::$app->getModule(Application::SERVICE_BILLING_ID);
        } else {
            throw new ErrorException('Не удалось определить провайдера данных сервиса биллинговой системы.');
        }
    }


    /**
     * @return null|object|BillingInterface
     *
     * @throws ErrorException
     * @throws \yii\base\InvalidConfigException
     */
    public static function getBillingProvider()
    {
        return self::billing()->get(Application::SERVICE_PROVIDER_ID);
    }


    /**
     * Если определён модуль биллинговой системы и настроен компонент службы взаимодействия с данными.
     *
     * @return bool
     *
     * @throws \yii\base\InvalidConfigException
     */
    private static function _billingExists()
    {
        if (empty(self::$_billingExists)) {
            $providerModule = \Yii::$app->getModule(Application::SERVICE_BILLING_ID);
            self::$_billingExists = !empty($providerModule) &&
                $providerModule->has(Application::SERVICE_PROVIDER_ID) &&
                array_key_exists(Application::SERVICE_INTERFACE,
                    class_implements($providerModule->get(Application::SERVICE_PROVIDER_ID)));
        }
        return self::$_billingExists;
    }


    /**
     * Возвращает компонент службы взаимодействия с SMSC.
     *
     * @return null|object|SmscInterface
     *
     * @throws ErrorException
     * @throws \yii\base\InvalidConfigException
     */
    public static function smsc()
    {
        if (self::_smscExists()) {
            return \Yii::$app->getModule(Application::SERVICE_SMSC_ID)->get(Application::SERVICE_PROVIDER_ID);
        } else {
            throw new ErrorException('Не удалось определить провайдера данных сервиса SMSC.');
        }
    }


    /**
     * Если определён модуль SMSC и настроен компонент службы взаимодействия.
     *
     * @return bool
     *
     * @throws \yii\base\InvalidConfigException
     */
    private static function _smscExists()
    {
        if (empty(self::$_smscExists)) {
            $providerModule = \Yii::$app->getModule(Application::SERVICE_SMSC_ID);
            self::$_smscExists = !empty($providerModule) &&
                $providerModule->has(Application::SERVICE_PROVIDER_ID) &&
                array_key_exists(Application::SERVICE_INTERFACE,
                    class_implements($providerModule->get(Application::SERVICE_PROVIDER_ID)));
        }
        return self::$_smscExists;
    }


    /**
     * Singleton constructor
     */
    public function __construct()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new static();
        }
        return self::$_instance;
    }


    /**
     * closed
     */
    public function __clone()
    {
    }


    /**
     * closed
     */
    public function __sleep()
    {
    }


    /**
     * closed
     */
    public function __wakeup()
    {
    }

}