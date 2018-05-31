<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\modules\tracker;

use modular\common\Application;
use modular\panel\PanelModule;

/**
 * Class TrackerModule
 *
 * @package modular\modules\tracker
 */
class TrackerModule extends PanelModule
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
                            . \Yii::t("common", "Tracker")
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