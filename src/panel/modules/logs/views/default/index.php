<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

use panel\widgets\PanelListView;
use yii\data\ActiveDataProvider;
use yii\web\View;


/* @var $this View */
/** @var ActiveDataProvider $dataProvider провайдер данных клиентов */
/** @var int $activeTracks */
/** @var string $searchForm */

$this->title = 'Логи запросов';
$this->params['breadcrumbs'][] = $this->title;
?>
<?/*= \yii\bootstrap\Alert::widget(
    [
        'closeButton' => false,
        'body'        => 'Интервал хранения записей логов API: <strong>1800</strong> с. Указанное значение устанавливается в <a href="/config" class="revert red">системных настройках</a>.',
        'options'     =>
            [
                'class' => 'alert alert-info',
            ],
    ]
)*/ ?>
<div class="panel panel-default">
    <div class='panel-heading'>
        <?= $searchForm ?>
    </div>
    <div class="panel-body">
        <?= PanelListView::widget([
            'dataProvider' => $dataProvider,
            'useSelection' => false,
            'itemView'     => '_item',
        ]); ?>
    </div>
</div>