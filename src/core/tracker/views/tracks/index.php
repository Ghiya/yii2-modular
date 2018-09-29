<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */


use modular\panel\widgets\PanelListView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\web\View;


/* @var $this View */
/** @var ActiveDataProvider $dataProvider */
/** @var int $active */

$this->params['breadcrumbs'][] = 'Список';
?>
<div class="panel panel-default">
    <div class="panel-body">
        <div class="panel-manage">
            <div class="text-center">
                <?=
                Html::a(
                    $active > 0 ?
                        "Отметить [ $active ] как просмотренные" :
                        'Отметить как просмотренные',
                    ["viewed-all",],
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
            </div>
        </div>
        <?=
        PanelListView::widget(
            [
                'dataProvider' => $dataProvider,
                'useSelection' => false,
                'itemView'     => '_item',
            ]
        );
        ?>
    </div>
</div>