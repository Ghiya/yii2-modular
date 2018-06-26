<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\panel\modules\tracker;

use modular\core\Application;
use modular\panel\PanelModule;

/**
 * Class Module
 * Модуль панели администрирования трекера уведомлений веб-ресурсов.
 *
 * @package modular\panel\modules\tracker
 */
class Module extends PanelModule
{


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        \Yii::configure($this, require __DIR__ . '/config/config.php');
        // panel menu items
        if ($this->_getApp()->isBackend) {
            $this->_getApp()->params['panelItems'][] =
                [
                    [
                        'label'   => '<span class="pull-left"><i class="fa fa-envelope-o"></i></span><span class="pull-right">'
                            . "Tracker"
                            . '</span>',
                        'url'     => ["/tracker"],
                        'encode'  => false,
                        'active'  => (boolean)preg_match("/tracker/i", \Yii::$app->request->url),
                        'options' => ['class' => 'clearfix', 'data' => ['spinner' => 'true']],
                    ],
                ];
        }
    }


    /**
     * @return \yii\console\Application|\yii\web\Application|Application
     */
    private function _getApp()
    {
        return \Yii::$app;
    }

}