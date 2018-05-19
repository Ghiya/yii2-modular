<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\panel\modules\tracks;


use modular\panel\models\UserRole;

class Module extends \modular\panel\modules\Module
{


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        // меню администрирования
        if (\Yii::$app->user->can(UserRole::PM_VIEW_DEBUG_DATA)) {
            foreach (\Yii::$app->panels as $panel) {

                $this->panelItems = [
                    [
                        'label'   => '<span class="pull-left"><i class="fa fa-envelope-o"></i></span><span class="pull-right">' . $panel->title . '</span>',
                        'url'     => ["/modules.tracks/$panel->id"],
                        'encode'  => false,
                        'active'  => (boolean)preg_match("/modules.tracks\/$panel->id/i", \Yii::$app->request->url),
                        'options' => ['class' => 'clearfix', 'data' => ['spinner' => 'true']],
                    ],
                ];
            }
        }
    }

}