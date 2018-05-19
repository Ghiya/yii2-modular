<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\panel\modules\users;


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
        if (\Yii::$app->user->can(UserRole::PM_MANAGE_USERS)) {
            $this->panelItems = [
                [
                    'label'   => '<span class="pull-left"><i class="fa fa-user-o"></i></span><span class="pull-right">Список</span>',
                    'url'     => ["/$this->id"],
                    'encode'  => false,
                    'active'  => (boolean)preg_match("/$this->id/i", \Yii::$app->request->url),
                    'options' => ['class' => 'clearfix', 'data' => ['spinner' => 'true']],
                ],
            ];
        }
    }

}