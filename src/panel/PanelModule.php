<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\panel;

use modular\core\helpers\ArrayHelper;
use modular\core\helpers\Html;
use modular\core\Module;
use modular\core\tracker\models\TrackData;


/**
 * Class Module
 * Базовый класс модуля панели администрирования веб-ресурса.
 *
 * @property-read PanelApplication $module
 *
 * @package modular\panel
 */
abstract class PanelModule extends Module
{


    /**
     * @return array
     */
    abstract protected function menuItems();


    /**
     * @return string
     */
    abstract protected function menuPermission();


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        // add navigation items for the authorized user ONLY
        if ($this->accessAllowed()) {
            $this->module->navigation =
                [
                    'id'          => $this->id,
                    'title'       => $this->title,
                    'description' => $this->description,
                    'urls'        => ArrayHelper::renameKeys($this->urls, ['is_active' => 'isActive']),
                    'version'     => $this->version,
                    'active'      => (boolean)preg_match("/\/$this->id/i", \Yii::$app->request->url),
                    'tracks'      => $this->getActiveTracks(),
                    'items'       =>
                        ArrayHelper::merge(
                            $this->trackItem(),
                            $this->menuItems()
                        ),
                ];
        }
    }


    protected function trackItem()
    {
        /** @var PanelApplication $app */
        $app = \Yii::$app;
        // if module is the resource package
        if ($this->hasTracking()) {
            $tracks = $this->getActiveTracks();
            return
                [
                    [
                        'label'   =>
                            Html::tag(
                                'span',
                                Html::tag('i', '', ['class' => 'fa fa-envelope-o']),
                                [
                                    'class' => 'pull-left'
                                ]
                            ) .
                            Html::tag(
                                'span',
                                $tracks > 0 ? "Уведомления [ $tracks ]" : "Уведомления",
                                [
                                    'class' => 'pull-right'
                                ]
                            ),
                        'encode'  => false,
                        'url'     => "/$this->id/tracks/list?id=$this->cid",
                        'active'  => (boolean)preg_match("/\/$this->id\/tracks/i", \Yii::$app->request->url),
                        'options' =>
                            [
                                'class' => 'clearfix',
                                'data'  =>
                                    [
                                        'spinner' => 'true'
                                    ]
                            ]
                    ]
                ];
        }
        return [];
    }


    public function hasTracking()
    {
        /** @var PanelApplication $app */
        $app = \Yii::$app;
        return
            preg_match("/" . $app->getPackagePrefix() . "/", $this->id);
    }


    /**
     * @return int
     */
    protected function getActiveTracks()
    {
        return
            $this->hasTracking() ?
                TrackData::countActive($this->cid, \Yii::$app->user->id) : 0;
    }


    /**
     * @return bool
     */
    protected function accessAllowed()
    {
        $permissions = (array)$this->menuPermission();
        if (!empty($permissions)) {
            foreach ($permissions as $permission) {
                if (\Yii::$app->user->can($permission)) {
                    return true;
                }
            }
            return false;
        }
        return true;
    }

}