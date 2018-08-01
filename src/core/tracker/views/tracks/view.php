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
</div>