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

/* @var $this View */
/** @var UserDataForm $model */
/** @var bool $editAllowed */

$this->title = 'Профиль';
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
        <p class="panel-title"><?= $model->name ?></p>
    </div>
    <div class="panel-body">
        <?=
            $form
                ->field($model, 'username')
                ->textInput(
                    $editAllowed ?
                        [] :
                        ['disabled' => 'disabled',]
                )
        ?>

        <?=
            $form
                ->field($model, 'password')
                ->passwordInput(
                    $editAllowed ?
                        [] :
                        ['disabled' => 'disabled',]

                )
        ?>

        <?=
            $form
                ->field($model, 'role', ['options' => ['class' => 'form-group clearfix',]])
                ->dropDownList(
                    UserRole::rolesList(),
                    [
                        'class'  =>
                            $editAllowed ?
                                'wide' :
                                'wide disabled',
                        'prompt' => '-',
                    ]
                )
        ?>

        <?=
            $form
                ->field($model, 'status', ['options' => ['class' => 'form-group clearfix',]])
                ->dropDownList(
                    User::states(),
                    [
                        'class'  =>
                            $editAllowed ?
                                'wide' :
                                'wide disabled',
                        'prompt' => '-',
                    ]
                )
        ?>

        <?=
            $form
                ->field($model, 'name')
                ->textInput(
                $editAllowed ?
                    [] :
                    ['disabled' => 'disabled',]
                )
        ?>

        <?=
            $form
                ->field($model, 'email')
                ->textInput(
                    $editAllowed ?
                        [] :
                        ['disabled' => 'disabled',]
                )
        ?>
    </div>
    <? if ( $editAllowed ) : ?>
        <div class="panel-footer clearfix">
            <?= Html::submitButton('Сохранить',
                ['class' => 'btn btn-success pull-left']) ?>
            <?= Html::a('отмена', ['/' . \Yii::$app->controller->module->id], ['class' => 'btn btn-default pull-right',]) ?>
        </div>
    <? endif; ?>
</div>
<?php ActiveForm::end(); ?>
