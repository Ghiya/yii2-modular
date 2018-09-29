<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

use modular\core\tracker\models\TrackData;

/** @var TrackData $model */
/** @var array $request */

?>
<ul class="list-group">
    <li class="list-group-item">
        <p class="font-book">
        <span class="text-backwards">
            <?= $model->user_ip ?> <strong class="text-backwards"><?= $model->version ?></strong>
        </span>
            <br/>
            <strong class="green">
                <?= strtoupper($model->request_method) ?>
            </strong> <?= "$model->module_id/$model->controller_id/$model->action_id" ?>
        </p>
    </li>
    <?php foreach ($request as $field => $value)  : ?>
        <li class="list-group-item">
            <p class="list-group-item-text font-book">
                <span class='text-backwards'>`<?= $field ?>` : </span>
                <?= !empty($value) ? $value : 'null' ?>
            </p>
        </li>
    <? endforeach; ?>
</ul>
