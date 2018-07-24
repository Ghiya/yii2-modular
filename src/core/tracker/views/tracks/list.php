<?php
/**
 * @copyright Copyright (c) 2014-2018 ООО "Глобал Телеком". Все права защищены.
 * @author    Ghiya Mikadze <gmikadze@v-tell.com>
 */

use modular\core\helpers\ArrayHelper;
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
/** @var array $filterUrlRoute */

$this->params['breadcrumbs'][] = 'Список';
?>
<div class="text-center">
    <?=
    Html::a(
        'Просмотрены все',
        Url::toRoute(
            $filterUrlRoute
        ),
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
            <? $linkClass = !empty($range['active']) ? 'btn btn-success font-bold' : 'btn btn-default font-thin disabled' ?>
        <? else: ?>
            <? $linkClass = !empty($range['active']) ? 'btn btn-success font-bold' : 'btn btn-default font-light' ?>
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