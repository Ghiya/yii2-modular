<?php
/**
 * @copyright Copyright (c) 2014-2018 ООО "Глобал Телеком". Все права защищены.
 * @author    Ghiya Mikadze <gmikadze@v-tell.com>
 */

use modular\core\helpers\Html;
use modular\core\tracker\models\SearchTrackData;
use yii\web\View;


/* @var $this View */
/** @var SearchTrackData $model */
/** @var string $type */
/** @var int $userId */
/** @var string $debugData */

$userId = \Yii::$app->user->identity->getId();
?>
<div class="col-xs-12">
    <div class="well text-left <?= $model->priority > SearchTrackData::PRIORITY_NOTICE ? 'red' : 'green' ?>">
        <?= preg_replace("/\r\n/i", "<br/>", $model->message) ?>
    </div>
</div>
<div class="col-xs-12">
    <div class="text-left">
        <pre>
Параметры ресурса:

<?= !empty($model->resource_id) ? "Resource: $model->resource_id\r\n" : "" ?>
<?= "Module/version: $model->module_id/$model->version\r\n" ?>
<?= "Route: $model->controller_id/$model->action_id" ?>
        </pre>
    </div>
    <? if (!empty($debugData)) : ?>
        <?=
        Html::a(
            'Параметры запроса</span>',
            "#collapsible-id$model->id",
            [
                'class' => 'btn btn-link',
                'data'  => [
                    'toggle' => 'modal',
                ]
            ]
        ) ?>
        <br/>
        <?=
        Html::tag(
            'div',
            Html::tag(
                'div',
                Html::tag(
                    'div',
                    Html::tag(
                        'div',
                        $debugData,
                        [
                            'class' => 'modal-body',
                        ]
                    ) .
                    Html::tag(
                        'div',
                        Html::button(
                            'Закрыть',
                            [
                                'class' => 'btn btn-default form-control',
                                'data'  =>
                                    [
                                        'dismiss' => 'modal'
                                    ]
                            ]
                        ),
                        [
                            'class' => 'modal-footer'
                        ]
                    ),
                    [
                        'class' => 'modal-content',
                    ]
                ),
                [
                    'class' => 'modal-dialog modal-lg text-left',
                    'role'  => 'document'
                ]
            ),
            [
                'id'       => "collapsible-id$model->id",
                'class'    => 'modal fade',
                'tabindex' => -1,
                'role'     => 'dialog'
            ]
        ) ?>
    <? endif; ?>
</div>