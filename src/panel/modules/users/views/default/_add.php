<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

use modular\panel\models\User;
use modular\panel\models\UserRole;
use modular\panel\modules\users\models\UserDataForm;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/** @var View $this */
/** @var UserDataForm $model */

$this->title = 'Добавить';
$this->params['breadcrumbs'][] = \Yii::$app->controller->module->params['bundleParams']['title'];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin(
    [
        'enableClientValidation' => false,
    ]
); ?>
<div id="cpanel-item-add" class="panel panel-default">
    <div class="panel-heading">
        <p class="panel-title"><?= $this->title ?></p>
    </div>
    <div class="panel-body">
        <?= $form->field($model, 'username')->textInput() ?>

        <?= $form->field($model, 'password')->passwordInput() ?>

        <?= $form
            ->field($model, 'role', ['options' => ['class' => 'form-group clearfix',]])
            ->dropDownList(
                UserRole::rolesList(),
                [
                    'class'  => 'wide',
                    'prompt' => '-',
                ]
            )
        ?>

        <?= $form->field($model, 'status', ['options' => ['class' => 'form-group clearfix',]])
            ->dropDownList(
                User::states(),
                [
                    'class'  => 'wide',
                ]
            )
        ?>

        <?= $form->field($model, 'name')->textInput() ?>

        <?= $form->field($model, 'email')->textInput() ?>
    </div>
    <div class="panel-footer clearfix">
        <?= Html::submitButton('Добавить',
            ['class' => 'btn btn-success pull-left']) ?>
        <?= Html::a('отмена', ['/' . \Yii::$app->controller->module->id], ['class' => 'btn btn-default pull-right',]) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>
