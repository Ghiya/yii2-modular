<?php
/**
 * @copyright Copyright (c) 2014-2018 ООО "Глобал Телеком". Все права защищены.
 * @author    Ghiya Mikadze <gmikadze@v-tell.com>
 */

use modular\core\tracker\models\TrackData;
use modular\core\tracker\views\View;
use modular\panel\models\UserRole;
use modular\panel\widgets\PanelItemModal;
use yii\helpers\Html;

/** @var View $this */
/** @var TrackData $model */

$isViewingByModule = $model->module->module_id == $this->context->module->cid;
echo PanelItemModal::widget(
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
                    'class' => $model->isViewed ?
                        'fa fa-envelope-open-o' :
                        'fa fa-envelope',
                ]
            ),
            [
                'class' => $model->priority > TrackData::PRIORITY_NOTICE ? 'red' : 'green',
            ]
        ),
        'shortDescription' => $isViewingByModule ? $model->version : $model->module->title,
        'fullDescription'  =>
            Html::tag(
                'span',
                $model->getDescription(),
                [
                    'class' =>
                        $model->priority > TrackData::PRIORITY_NOTICE ?
                            'red' : 'green'
                ]
            ),
        'lastRow'          => Html::tag(
            'p',
            \Yii::$app->formatter->asDatetime($model->created_at, "php:d.m.Y / H:i:s"),
            ['class' => 'text-center',]
        ),
    ]
);
