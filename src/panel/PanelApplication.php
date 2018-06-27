<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\panel;


use modular\core\Application;
use modular\core\models\PackageInit;
use yii\base\InvalidConfigException;


/**
 * Class PanelApplication
 * Приложение администрирования веб-ресурсов.
 *
 * @property array $navigation массив элементов меню панелей администрирования
 *
 * @package modular\panel
 */
class PanelApplication extends Application
{


    /**
     * @var array
     */
    private $_navigation = [];


    /**
     * {@inheritdoc}
     *
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\ServerErrorHttpException
     */
    public function bootstrap()
    {
        // parent bootstrapping always goes first because of the modules installing as extensions
        parent::bootstrap();
        // add all modules packages for the authorized user
        if (!\Yii::$app->user->isGuest) {
            foreach (PackageInit::getParams() as $params) {
                $this->addPackage($params);
            }
        }
    }


    /**
     * Setter for the panel menu items.
     *
     * @param array $params
     *
     * @throws InvalidConfigException
     */
    public function setNavigation($params = [])
    {
        if (empty($params['id'])) {
            throw new InvalidConfigException("Array must contain `id` value.");
        }
        $this->_navigation[$params['id']] = $params;
    }


    /**
     * Getter for the panel menu items.
     *
     * @param bool $keysOnly
     *
     * @return array
     */
    public function getNavigation($keysOnly = false)
    {
        return
            $keysOnly ?
                array_keys($this->_navigation) :
                array_values($this->_navigation);
    }

}