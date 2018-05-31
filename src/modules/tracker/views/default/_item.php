<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

use modular\panel\models\UserRole;
use panel\modules\tracks\models\Track;
use modular\panel\widgets\PanelItemModal;
use yii\helpers\Html;

/** @var Track $model */

?>
<?= PanelItemModal::widget([
    'removeAllowed'    => \Yii::$app->getUser()->can(UserRole::PM_REMOVE_RESOURCE_DATA),
    'useSelection'     => false,
    'itemId'           => $model->id,
    'itemType'         => 'tracks',
    'listLinkType'     => PanelItemModal::LIST_LINK_TYPE_COLLAPSE,
    'firstRow'         => Html::tag(
        'p',
        Html::tag(
            'i',
            null,
            [
                'class' => ($model->hasBeenViewedBy(\Yii::$app->user->identity->id)) ?
                    'fa fa-envelope-open-o' :
                    'fa fa-envelope',
            ]
        ),
        [
            'class' => ($model->priority > 1) ? 'text-danger text-center' : 'text-success',
        ]
    ),
    'shortDescription' => "[ <strong>$model->id</strong> ] $model->decodedPriority",
    'lastRow'          => Html::tag(
        'p',
        \Yii::$app->formatter->asDatetime($model->created_at, "php:d.m.Y / H:i:s"),
        ['class' => 'text-center',]
    ),
]) ?>
