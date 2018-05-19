<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

return [
    'version'       => 'v0.1.0',
    'defaultRoute'  => 'modules.logs/default/index',
    'controllerMap' => [
        'default' => 'modular\panel\modules\logs\controllers\DefaultController',
    ],
    'components'    => [
        'urlManager' => [
            'class' => '\yii\web\UrlManager',
            'rules' => [
                'GET modules.logs'                     => 'modules.logs/default/index',
                'GET modules.logs/view'                => 'modules.logs/default/view',
                'POST modules.logs/delete-selected'    => 'modules.logs/default/delete-selected',
                'POST modules.logs/delete'             => 'modules.logs/default/delete',
                'GET modules.logs/<providerId>/search' => 'modules.logs/default/search',
                'GET modules.logs/<providerId>'        => 'modules.logs/default/index',
            ],
        ],
    ],
    'params'        => [
        'defaults' => [
            'language' => 'ru-RU',
        ],
    ],
];