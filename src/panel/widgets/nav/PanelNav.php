<?php

namespace modular\panel\widgets\nav;


use modular\panel\models\UserRole;
use yii\bootstrap\Nav;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;


/**
 *
 * Class Nav виджет меню навигационной панели административного приложения системы.
 *
 * @package modular\panel\widgets\nav
 * @author  Ghiya Mikadze <ghiya@mikadze.me>
 */
class PanelNav extends Nav
{


    /**
     * @inheritdoc
     *
     * Устанавливает настройки стандартного виджета и пункты главного меню системы.
     * Пункты меню микшируются с массивом установленным в [[$items]].
     */
    public function init()
    {
        parent::init();
        $this->items = ArrayHelper::merge($this->initPanelNavItems(), $this->items);
        $this->options = ['class' => 'navbar-nav navbar-right nav'];
    }


    /**
     * Возвращает массив с пунктами главного меню системы в зависимости от статуса пользователя.
     *
     * @return array
     */
    protected function initPanelNavItems()
    {
        $panelNavItems = [];
        return
            \Yii::$app->user->isGuest ?
                [] :
                ArrayHelper::merge(
                    $panelNavItems,
                    \Yii::$app->request->url == "/" ?
                        [
                            [
                                "label"       => 'провайдеры',
                                "encode"      => false,
                                'active'      => true,
                                'url'         => '#providers',
                                "options"     =>
                                    [
                                        "role" => "presentation",
                                    ],
                                'linkOptions' =>
                                    [
                                        "aria-controls" => "providers",
                                        "role"          => "tab",
                                        "data"          =>
                                            [
                                                "toggle" => "tab",
                                            ]
                                    ]
                            ],
                            [
                                "label"       => 'веб-ресурсы',
                                "encode"      => false,
                                'url'         => '#panels',
                                "options"     =>
                                    [
                                        "role" => "presentation",
                                    ],
                                'linkOptions' =>
                                    [
                                        "aria-controls" => "panels",
                                        "role"          => "tab",
                                        "data"          =>
                                            [
                                                "toggle" => "tab",
                                            ]
                                    ]
                            ],
                            [
                                "label"       => 'сервисы',
                                "encode"      => false,
                                'url'         => '#services',
                                "options"     =>
                                    [
                                        "role" => "presentation",
                                    ],
                                'linkOptions' =>
                                    [
                                        "aria-controls" => "services",
                                        "role"          => "tab",
                                        "data"          =>
                                            [
                                                "toggle" => "tab",
                                            ]
                                    ],
                                "visible"     => \Yii::$app->user->can(UserRole::PM_VIEW_DEBUG_DATA),
                            ],
                            [
                                "label"       => 'профиль',
                                "encode"      => false,
                                'url'         => '/modules.users/view?id=' . \Yii::$app->user->identity->getId(),
                                "options"     =>
                                    [
                                        "role" => "presentation",
                                    ],
                                'linkOptions' =>
                                    [
                                        "role" => "tab",
                                    ],
                            ],
                            [
                                'label'       => '<i class="fa fa-sign-out"></i>выйти [ <strong>' . \Yii::$app->user->identity->username . '</strong> ]',
                                'url'         => Url::toRoute('/logout'),
                                'encode'      => false,
                                'linkOptions' => [
                                    'data' => ['method' => 'post',]
                                ],
                            ],
                        ] :
                        [
                            [
                                "label"       => 'профиль',
                                "encode"      => false,
                                "active"      => preg_match("/users/i", \Yii::$app->request->url),
                                'url'         => '/modules.users/view?id=' . \Yii::$app->user->identity->getId(),
                                "options"     =>
                                    [
                                        "role" => "presentation",
                                    ],
                                'linkOptions' =>
                                    [
                                        "role" => "tab",
                                    ],
                            ],
                            [
                                'label'       => '<i class="fa fa-sign-out"></i>выйти [ <strong>' . \Yii::$app->user->identity->username . '</strong> ]',
                                'url'         => Url::toRoute('/logout'),
                                'encode'      => false,
                                'linkOptions' => [
                                    'data' => ['method' => 'post',]
                                ],
                            ],
                        ]
                );
    }
}