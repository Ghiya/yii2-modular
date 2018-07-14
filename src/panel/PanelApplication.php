<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\panel;


use modular\core\Application;
use modular\core\helpers\ArrayHelper;
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
        // add all modules packages
        foreach (PackageInit::getParams() as $params) {
            $this->addPackage($params);
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
        if (empty($params['panelGroup'])) {
            throw new InvalidConfigException("Array must contain `panelGroup` value.");
        }
        $this->_navigation[] = $params;
    }


    /**
     * Getter for the panel menu items.
     *
     * @param string $panelGroup
     * @param bool   $keysOnly
     *
     * @return array
     */
    public function getNavigation($panelGroup = "", $keysOnly = false)
    {
        return
            $keysOnly ?
                array_keys(
                    ArrayHelper::index(
                        $this->_navigation,
                        'id'
                    )
                ) :
                ArrayHelper::filter(
                    ArrayHelper::index(
                        $this->_navigation,
                        'id',
                        [
                            function ($element) {
                                return $element['panelGroup'];
                            }
                        ]
                    ),
                    !empty($panelGroup) ? [$panelGroup] : []
                );
    }


    /**
     * Getter for the panel menu items list.
     *
     * @return array
     */
    public function getNavigationList()
    {
        return $this->_navigation;
    }

}