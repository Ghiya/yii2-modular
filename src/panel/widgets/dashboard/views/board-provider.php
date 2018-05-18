<?php
/**
 * @copyright Copyright (c) 2017. Ghiya Mikadze <ghiya@mikadze.me>
 */

use yii\web\View;
use modular\panel\modules\_default\Module;
use yii\helpers\Html;

/** @var View $this */
/** @var Module $boardItem */
/** @var string $permission */
?>
<? $state = $boardItem->state; ?>
    <div class="panel-heading clearfix">
        <? if (!empty($boardItem->panelItems['items'])) : ?>
            <h5 class="panel-title pull-right"
                data-target="#panel-collapse-<?= $boardItem->safeId ?>"
                data-toggle="collapse"
                aria-expanded="false">
                <a href="javascript:void(0);" class="<?= !empty($state) ? 'active' : 'inactive' ?>">
                    <?= $boardItem->params['bundleParams']['title'] ?>
                </a>
            </h5>
        <? else: ?>
            <h5 class="panel-title pull-right"
                aria-expanded="false">
                <span class="<?= !empty($state) ? 'active' : 'inactive' ?>">
                    <?= $boardItem->params['bundleParams']['title'] ?>
                </span>
            </h5>
        <? endif; ?>
        <div class="pull-left">
            <? if (!empty($boardItem->params['bundleParams']['description'])) : ?>
                <?= Html::tag(
                    'p',
                    $boardItem->params['bundleParams']['description'],
                    [
                        'class' => 'description',
                    ]
                ) ?>
            <? endif; ?>
            <?= Html::tag(
                'p',
                $boardItem->version,
                [
                    'class' => 'version',
                ]
            ) ?>
        </div>
    </div>
<? if (!empty($boardItem->panelItems['items'])) : ?>
    <div class="collapse"
         id="panel-collapse-<?= $boardItem->safeId ?>">
        <div class="panel-body">
            <p class="background-badge">
                <i class="fa fa-database"></i>
            </p>
            <ul class="nav nav-pills nav-stacked">
                <? foreach ($boardItem->panelItems['items'] as $item) : ?>
                    <li role="presentation">
                        <?= Html::a(
                            $item['label'],
                            $item['url'],
                            $item['options']
                        ) ?>
                    </li>
                <? endforeach; ?>
            </ul>
            <? if (empty($permission) || \Yii::$app->user->can($permission)) : ?>
                <? if (!empty($state)) : ?>
                    <pre>Данные соединения:<?=
                        "\r\n\r\n" . trim($state[0]) .
                        "\r\n" . \Yii::$app->formatter->asDatetime($state[1],
                            "php:H:i:s d.m.Y") ?></pre>
                <? endif; ?>
            <? endif; ?>
        </div>
    </div>
<? endif; ?>