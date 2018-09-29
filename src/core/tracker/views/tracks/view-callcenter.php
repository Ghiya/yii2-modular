<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
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