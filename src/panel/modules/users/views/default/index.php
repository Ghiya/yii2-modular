<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

use modular\panel\widgets\PanelListView;
use yii\data\ActiveDataProvider;
use yii\web\View;
use yii\helpers\Html;
use modular\panel\models\UserRole;


/* @var $this View */
/** @var ActiveDataProvider $dataProvider провайдер данных клиентов */

$this->title = \Yii::$app->controller->module->title;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="panel panel-default">
    <? if (\Yii::$app->user->can(UserRole::PM_REMOVE_RESOURCE_DATA)) : ?>
        <div class="panel-heading clearfix">
            <div class="col-xs-12 col-md-4 col-sm-3 col-lg-2">
                <?= Html::a('<i class="fa fa-plus"></i>', ['/' . \Yii::$app->controller->module->id . '/add',], ['class' => 'btn btn-default form-control',]) ?>
            </div>
            <div class="col-xs-12 col-md-4 col-sm-3 col-lg-2 col-md-push-4 col-sm-push-6 col-lg-push-8">
                <?= Html::a('<i class="fa fa-refresh"></i>', ['/' . \Yii::$app->controller->module->id . '/refresh',], ['class' => 'btn btn-success form-control',]) ?>
            </div>
        </div>
    <? endif; ?>
    <div class="panel-body">
        <?= PanelListView::widget([
            'dataProvider' => $dataProvider,
            'useSelection' => false,
            'itemView'     => '_item',
        ]); ?>
    </div>
</div>