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
/** @var array $filterUrlRoute */
/** @var string $filterForm */

$this->params['breadcrumbs'][] = 'Список';
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h2 class="panel-title">
            ФИЛЬТР
        </h2>
    </div>
    <div class="panel-body">
        <?= $filterForm ?>
        <? if (!empty($searchRanges)) : ?>
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
                    <? if (empty($range['total'])) : ?>
                        <? $linkClass = !empty($range['current']) ? 'btn btn-success font-bold' : 'btn btn-default font-thin disabled' ?>
                    <? else: ?>
                        <? $linkClass = !empty($range['current']) ? 'btn btn-success font-bold' : 'btn btn-default font-light' ?>
                    <? endif; ?>
                    <?=
                    Html::a(
                        !empty($range['active']) ?
                            $range['date'] . ' [ ' . $range['total'] . ' / <strong>' . $range['active'] . '</strong> ]' :
                            $range['date'] . ' [ ' . $range['total'] . ' ]',
                        Url::toRoute(
                            [
                                "list",
                                "cid"  => $resourceId,
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
        <? endif; ?>
        <?=
        PanelListView::widget(
            [
                'dataProvider' => $dataProvider,
                'useSelection' => false,
                'itemView'     => '_item',
            ]
        )
        ?>
    </div>
</div>