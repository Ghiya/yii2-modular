<?php
/**
 * @copyright Copyright (c) 2014-2018 ООО "Глобал Телеком". Все права защищены.
 * @author Ghiya Mikadze <gmikadze@v-tell.com>
 */

use modular\panel\models\UserRole;
use modular\core\tracker\models\SearchTrackData;
use modular\panel\widgets\PanelItemModal;
use yii\helpers\Html;

/** @var SearchTrackData $model */

?>
<?=
    PanelItemModal::widget(
        [
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
            'shortDescription' => "[ <strong>$model->id</strong> ] " . $model->getPriorityLabel(),
            'lastRow'          => Html::tag(
                'p',
                \Yii::$app->formatter->asDatetime($model->created_at, "php:d.m.Y / H:i:s"),
                ['class' => 'text-center',]
            ),
        ]
    )
?>
