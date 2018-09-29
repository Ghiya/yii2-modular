<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

use panel\modules\logs\models\LogRecord;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/** @var LogRecord $model */
/** @var string $type */
/** @var int $userId */
/** @var string $debugData */

$userId = \Yii::$app->user->identity->id;
?>
<?= DetailView::widget([
    'model'      => $model,
    'options'    => [
        'class' => 'col-xs-12',
        'tag'   => 'div',
    ],
    'template'   => '<div class="text-left">{value}</div>',
    'attributes' => $model->viewFields(),
]); ?>
