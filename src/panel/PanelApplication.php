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
     * @var string
     */
    protected $packagePrefix = 'resources.';


    /**
     * @var array
     */
    private $_navigation = [];


    /**
     * {@inheritdoc}
     *
     * @throws \yii\web\ServerErrorHttpException
     */
    protected function getPackagesParams()
    {
        $packagesParams = [];
        // add all modules packages
        foreach (PackageInit::getParams() as $params) {
            $packagesParams[] = $params;
        }
        return $packagesParams;
    }


    /**
     * {@inheritdoc}
     */
    protected function prependedBeforeAction($event)
    {
        $this->setLanguageWithSession();
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
            throw new InvalidConfigException("Array must contain associated `id` key.");
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
        if ($keysOnly) {
            return
                array_keys(
                    ArrayHelper::index(
                        $this->_navigation,
                        'id'
                    )
                );
        }
        $indexedGroups =
            ArrayHelper::index(
                $this->_navigation,
                null,
                [
                    function ($element) {
                        $fullId = explode(".", $element['id']);
                        return $fullId[0];
                    }
                ]
            );
        return
            !empty($panelGroup) ?
                ArrayHelper::filter(
                    $indexedGroups,
                    [$panelGroup]
                ) :
                array_reverse($indexedGroups);
    }


}