<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

return [
    'version'       => 'v0.2.0',
    'defaultRoute'  => 'tracker/default/index',
    'controllerMap' => [
        'default' => 'modular\common\modules\tracks\controllers\DefaultController',
    ],
    'components'    => [
        'urlManager' => [
            'class' => '\yii\web\UrlManager',
            'rules' => [
                'GET tracker'                     => 'tracker/default/index',
                'GET tracker/view'                => 'tracker/default/view',
                'POST tracker/delete'             => 'tracker/default/delete',
                'GET tracker/viewed/<resourceId>' => 'tracker/default/viewed',
                'GET tracker/state/<resourceId>'  => 'tracker/default/state',
                'GET tracker/<resourceId>'        => 'tracker/default/index',
            ],
        ],
    ],
    'params'        => [
        'defaults' => [
            'language' => 'ru-RU',
        ],
    ],
];