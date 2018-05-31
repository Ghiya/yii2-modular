<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

use panel\modules\tracks\models\Track;
use yii\bootstrap\Alert;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;


/* @var $this View */
/** @var Track $model */
/** @var string $type */
/** @var int $userId */
/** @var string $debugData */

$userId = \Yii::$app->user->identity->id;
?>
<?= DetailView::widget([
    'model'      => $model,
    'template'   => '<div class="cpanel-item-property clearfix"><div class="wrapper">{value}</div></div>',
    'options'    => [
        'tag'   => 'div',
        'class' => 'cpanel-item-property-list text-left clearfix',
    ],
    'attributes' => [
        [
            'attribute' => 'message',
            'format'    => 'raw',
            'value'     => empty($debugData) ?
                '<br/>' .
                Alert::widget(
                    [
                        'body'        =>
                            preg_replace("/\r\n/i", "<br/>", $model->message),
                        'closeButton' => false,
                        'options'     =>
                            [
                                'class' => ($model->priority > 1) ? 'alert alert-danger text-left' : 'alert alert-success text-left',
                            ],
                    ]
                ) .
                $model->getRelatedLink() :
                '<br/>' .
                Alert::widget(
                    [
                        'body'        =>
                            preg_replace("/\r\n/i", "<br/>", $model->message),
                        'closeButton' => false,
                        'options'     =>
                            [
                                'class' => ($model->priority > 1) ? 'alert alert-warning text-left' : 'alert alert-success text-left',
                            ],
                    ]
                ) .
                $model->getRelatedLink() .
                Html::a(
                    'Подробнее</span>',
                    "#collapsible-id$model->id",
                    [
                        'class'         => 'revert',
                        'data'          => [
                            'toggle'   => 'collapse',
                            'controls' => "collapsible-id$model->id"
                        ],
                        'aria-expanded' => 'false',
                    ]
                ) .
                '<br/><br/>' .
                Html::tag(
                    'div',
                    Html::decode(preg_replace("/\r\n/i", "<br/>", $debugData)),
                    [
                        'id'    => "collapsible-id$model->id",
                        'class' => 'collapse',
                    ]
                ),
        ],
    ],
]); ?>