<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */


use modular\core\helpers\ArrayHelper;
use modular\core\helpers\Html;
use modular\core\tracker\models\SearchTrackData;
use yii\bootstrap\ActiveForm;

/** @var SearchTrackData $model */
/** @var bool $isActive */
?>
<div class="panel-search">
    <?
    $form = ActiveForm::begin(
        [
            'action' => ArrayHelper::merge(['filter',], \Yii::$app->request->get()),
            'method' => 'get',
        ]
    ) ?>
    <?= $form->field(
        $model,
        'trackId',
        [
            'options' =>
                [
                    'class' => 'form-group col-xs-12 col-sm-4',
                ],
        ]
    ) ?>
    <?= Html::tag(
        'div',
        Html::tag(
            'label',
            '&nbsp;',
            ['class' => 'control-label',]
        ) .
        Html::a(
            'Сбросить',
            ['list', 'cid' => \Yii::$app->request->get('cid')],
            [
                'class' =>
                    $isActive ?
                        'form-control btn btn-default' :
                        'form-control btn btn-default disabled',
            ]
        ),
        ['class' => 'form-group col-xs-12 col-sm-4',]
    ); ?>
    <?= Html::tag(
        'div',
        Html::tag(
            'label',
            '&nbsp;',
            ['class' => 'control-label',]
        ) .
        Html::submitButton(
            'Применить',
            ['class' => 'form-control btn btn-success',]
        ),
        ['class' => 'form-group col-xs-12 col-sm-4',]
    ); ?>
    <? ActiveForm::end() ?>
</div>