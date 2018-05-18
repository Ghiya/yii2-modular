<?php
/**
 * @copyright Copyright (c) 2017. Ghiya Mikadze <ghiya@mikadze.me>
 */

use modular\panel\modules\_default\Module;
use yii\helpers\Html;
use yii\web\View;

/** @var View $this */
/** @var Module $boardItem */

?>
    <div class="panel-heading clearfix">
        <?= Html::tag(
            'h5',
            Html::a(
                $boardItem->params['bundleParams']['title'],
                !empty($boardItem->panelItems['items']) ?
                    "javascript:void(0);" : null
            ),
            [
                'class'         => 'panel-title pull-right',
                'data'          => [
                    'toggle' => 'collapse',
                    'target' => "#panel-collapse-" . $boardItem->safeId,
                ],
                'aria-expanded' => 'false',
            ]
        )
        ?>
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
                <i class="fa fa-cogs fa-5x"></i>
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
        </div>
    </div>
<? endif; ?>