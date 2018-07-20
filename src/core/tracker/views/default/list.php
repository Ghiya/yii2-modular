<?php
/**
 * @copyright Copyright (c) 2014-2018 ООО "Глобал Телеком". Все права защищены.
 * @author    Ghiya Mikadze <gmikadze@v-tell.com>
 */

use modular\panel\widgets\PanelListView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;


/* @var $this View */
/** @var ActiveDataProvider $dataProvider провайдер данных клиентов */
/** @var array $searchRanges */
/** @var int $active */
/** @var string $resourceId */

$this->params['breadcrumbs'][] = 'Список';
?>
<? /*= \yii\bootstrap\Alert::widget(
    [
        'closeButton' => false,
        'body'        => 'Интервал хранения уведомлений: <strong>1800</strong> с. Указанное значение устанавливается в <a href="/config" class="revert red">системных настройках</a>.',
        'options'     =>
            [
                'class' => 'alert alert-info',
            ],
    ]
)*/ ?>
<br/>
<div class="text-center">
    <?=
    Html::a(
        'Отметить все уведомления как просмотренные.',
        ["viewed?id=$resourceId",],
        [
            'data'  => [
                'confirm' => 'Все уведомления будут отмечены как просмотренные?',
            ],
            'class' => $active > 0 ? 'btn btn-default' : 'btn btn-default disabled'
        ]
    )
    ?>
</div>
<br/>
<div class="text-center">
    <? foreach ($searchRanges as $index => $range) : ?>
        <? if (empty($range['count'])) : ?>
            <? $linkClass = !empty($range['active']) ? 'btn btn-success font-book disabled' : 'btn btn-default font-thin disabled' ?>
        <? else: ?>
            <? $linkClass = !empty($range['active']) ? 'btn btn-success font-book' : 'btn btn-default font-book' ?>
        <? endif; ?>
        <?=
        Html::a(
            $range['date'] . ' [ ' . $range['count'] . ' ]',
            Url::toRoute(
                [
                    "list",
                    "id"   => $resourceId,
                    'from' => $range['from'],
                    'to'   => $range['to']
                ]
            ),
            [
                'class' => $linkClass
            ]
        )
        ?>
    <? endforeach; ?>
</div>
<br/>
<div class="panel panel-default">
    <div class="panel-body">
        <?= PanelListView::widget([
            'dataProvider' => $dataProvider,
            'useSelection' => false,
            'itemView'     => '_item',
        ]); ?>
    </div>
</div>