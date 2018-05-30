<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

use modular\panel\models\UserRole;
use modular\panel\widgets\PanelListView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\web\View;


/* @var $this View */
/** @var ActiveDataProvider $dataProvider провайдер данных клиентов */
/** @var int $activeTracks */
/** @var string $resourceId */

$this->title = 'Уведомления';
$this->params['breadcrumbs'][] = $this->title;
?>
<? /*= \yii\bootstrap\Alert::widget(
    [
        'closeButton' => false,
        'body'        => 'Интервал хранения уведомлений: <strong>1800</strong> с. Указанное значение устанавливается в <a href="/config" class="revert red">системных настройках</a>.',
        'options'     =>
            [
                'class' => 'alert alert-info',
            ],
    ]
)*/ ?>
<? if (\Yii::$app->user->can(UserRole::PM_VIEW_RESOURCE_DATA) && $activeTracks > 0) : ?>
    <ul class="list-group">
        <li class="list-group-item">
            <i class="fa fa-check"></i>
            <?=
            Html::a(
                'Отметить все уведомления как просмотренные.',
                ["viewed/$resourceId",],
                [
                    'data' => [
                        'confirm' => 'Все уведомления будут отмечены как просмотренные?',
                    ],
                ]
            )
            ?>
        </li>
    </ul>
<? endif; ?>
<div class="panel panel-default">
    <div class="panel-body">
        <?= PanelListView::widget([
            'dataProvider' => $dataProvider,
            'useSelection' => false,
            'itemView'     => '_item',
        ]); ?>
    </div>
</div>