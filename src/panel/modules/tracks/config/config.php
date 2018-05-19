<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

return [
    'version'       => 'v0.1.1',
    'defaultRoute'  => 'modules.tracks/default/index',
    'controllerMap' => [
        'default' => 'modular\panel\modules\tracks\controllers\DefaultController',
    ],
    'components'    => [
        'urlManager' => [
            'class' => '\yii\web\UrlManager',
            'rules' => [
                'GET modules.tracks'                     => 'modules.tracks/default/index',
                'GET modules.tracks/view'                => 'modules.tracks/default/view',
                'POST modules.tracks/delete'             => 'modules.tracks/default/delete',
                'GET modules.tracks/viewed/<resourceId>' => 'modules.tracks/default/viewed',
                'GET modules.tracks/state/<resourceId>'  => 'modules.tracks/default/state',
                'GET modules.tracks/<resourceId>'        => 'modules.tracks/default/index',
            ],
        ],
    ],
    'params'        => [
        'defaults' => [
            'language' => 'ru-RU',
        ],
    ],
];