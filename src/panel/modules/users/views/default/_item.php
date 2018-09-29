<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

use modular\panel\models\User;
use modular\panel\models\UserRole;
use modular\panel\widgets\PanelItemModal;
use yii\helpers\Html;


/** @var User $model */
switch ($model->status) {

    case User::STATUS_ACTIVE :
        $firstRowBadge = '<span class="green"><i class="fa fa-user-o"></i></span>';
        break;

    case User::STATUS_BLOCKED :
        $firstRowBadge = '<span class="red"><i class="fa fa-lock"></i></span>';
        break;

    case User::STATUS_DELETED :
        $firstRowBadge = '<span class="text-backwards"><i class="fa fa-user-times"></i></span>';
        break;

    default :
        $firstRowBadge = '<i class="fa fa-user"></i>';
        break;
}
?>
<?= PanelItemModal::widget([
    'useSelection'     => false,
    'removeAllowed'    => \Yii::$app->user->can(UserRole::PM_REMOVE_RESOURCE_DATA),
    'itemId'           => $model->id,
    'itemType'         => '/' . \Yii::$app->controller->module->id,
    'listLinkType'     => PanelItemModal::LIST_LINK_TYPE_DIRECT,
    'firstRow'         => Html::tag('p', $firstRowBadge),
    'shortDescription' => $model->name,
    'fullDescription'  => $model->username,
    'lastRow'          => Html::tag(
        'p',
        \Yii::$app->formatter->asDatetime($model->created_at, "php: d.m.Y H:i:s"),
        ['class' => 'text-center',]
    ),
]) ?>
