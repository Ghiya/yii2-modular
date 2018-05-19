<?
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

use panel\modules\logs\models\Search;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;

/** @var View $this */
/** @var Search $model */
/** @var \common\Application $app */
/** @var string $providerId */

$app = \Yii::$app;
$this->registerJs(
    'window.UILogs.items = ' .
    Json::htmlEncode(
        [
            'day'   => [
                'from'  => $model->days[0],
                'to'    => $model->days[count($model->days) - 1],
                'scale' => array_values($model->days),
            ],
            'month' => [
                'from'  => $model->months[0],
                'to'    => $model->months[count($model->months) - 1],
                'scale' => array_values($model->months),
            ],
            'year'  => [
                'from'  => $model->years[0],
                'to'    => $model->years[count($model->years) - 1],
                'scale' => array_values($model->years),
            ],
        ]
    ) .
    ';yii.initModule(window.UILogs);',
    $this::POS_READY
);
?>
<div class="clearfix">
    <?
    $form = ActiveForm::begin([
        'id'                 => 'cp-form-jrange',
        'action'             => ["$providerId/search"],
        'method'             => 'get',
        'enableClientScript' => false,
    ]) ?>
    <?= $form->field(
        $model,
        'day',
        [
            'options' =>
                [
                    'class' => 'form-group col-xs-12 col-sm-12 col-md-12 col-lg-12',
                ],
        ]
    )->hiddenInput() ?>
    <?= $form->field(
        $model,
        'month',
        [
            'options' =>
                [
                    'class' => 'form-group col-xs-12 col-sm-6 col-md-6 col-lg-6',
                ],
        ]
    )->hiddenInput() ?>
    <?= $form->field(
        $model,
        'year',
        [
            'options' =>
                [
                    'class' => 'form-group col-xs-12 col-sm-6 col-md-6 col-lg-6',
                ],
        ]
    )->hiddenInput() ?>
    <?php
    $moduleList = [];
    foreach ($app->resourceBundles as $bundle) {
        $moduleList[(string)$bundle->id] = $bundle->title;
    }
    echo $form->field($model, 'bundle_id', [
        'options'      =>
            [
                'class' => 'form-group col-xs-12 col-sm-8 col-md-8 col-lg-8',
            ],
        'inputOptions' =>
            [
                'class' => 'form-control wide',
            ],
    ])->dropDownList($moduleList, [
        'prompt' => 'Все доступные',
    ]) ?>
    <div class="form-group col-xs-12 col-sm-4 col-md-4 col-lg-4">
        <?= Html::tag('label', '&nbsp;',
            ['class' => 'control-label',]) . Html::submitButton('Поиск', [
            'encode' => false,
            'class'  => 'form-control btn btn-success',
        ]) ?>
    </div>
    <? ActiveForm::end() ?>
</div>