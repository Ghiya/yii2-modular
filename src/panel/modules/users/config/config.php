<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

return [
    'version'       => 'v0.1.1',
    'defaultRoute'  => 'modules.users/default/index',
    'controllerMap' => [
        'default' => 'modular\panel\modules\users\controllers\DefaultController',
    ],
    'components'    => [
        'urlManager' => [
            'class' => '\yii\web\UrlManager',
            'rules' => [
                'GET modules.users'         => 'modules.users/default/index',
                'modules.users/view'        => 'modules.users/default/view',
                'modules.users/add'         => 'modules.users/default/add',
                'modules.users/refresh'     => 'modules.users/default/refresh',
                'POST modules.users/delete' => 'modules.users/default/delete',
            ],
        ],
    ],
    'params'        => [
        'defaults' => [
            'language' => 'ru-RU',
        ],
    ],
];