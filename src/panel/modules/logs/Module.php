<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\panel\modules\logs;


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
            foreach (\Yii::$app->providers as $provider) {
                $this->panelItems = [
                    [
                        'label'   => '<span class="pull-left"><i class="fa fa-file-code-o"></i></span><span class="pull-right">' . $provider->title . '</span>',
                        'url'     => ["/modules.logs/$provider->id"],
                        'encode'  => false,
                        'active'  => (boolean)preg_match("/modules.logs\/$provider->id/i", \Yii::$app->request->url),
                        'options' => ['class' => 'clearfix', 'data' => ['spinner' => 'true']],
                    ],
                ];
            }
        }
    }

}