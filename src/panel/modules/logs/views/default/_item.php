<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

use modular\panel\models\UserRole;
use modular\common\models\ServiceLog;
use modular\panel\widgets\PanelItemModal;
use yii\helpers\Html;


/** @var ServiceLog $model */

?>
<?= PanelItemModal::widget([
    'useSelection'     => false,
    'removeAllowed'    => \Yii::$app->user->can(UserRole::PM_REMOVE_RESOURCE_DATA),
    'itemId'           => $model->id,
    'itemType'         => 'logs',
    'listLinkType'     => PanelItemModal::LIST_LINK_TYPE_COLLAPSE,
    'firstRow'         => Html::tag('p', '<i class="fa fa-code"></i>'),
    'shortDescription' => $model->bundle->title,
    'lastRow'          => Html::tag(
        'p',
        $model->createdAt,
        ['class' => 'text-center',]
    ),
]) ?>
