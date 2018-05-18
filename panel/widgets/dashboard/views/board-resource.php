<?php
/**
 * @copyright Copyright (c) 2017. Ghiya Mikadze <ghiya@mikadze.me>
 */

use yii\web\View;
use panel\modules\_default\Module;
use yii\helpers\Html;

/** @var View $this */
/** @var Module $boardItem */
/** @var string $permission */

$tracksContent = "";
/*if ( \Yii::$app->user->can($permission) ) {
    $trackerState = !empty($boardItem->tracker) ? $boardItem->trackerState : false;
    $tracksContent = Html::tag(
        'span',
        Html::a(
            (!empty($trackerState)) ?
                '<i class="fa fa-bell"></i>' :
                '<i class="fa fa-bell-o"></i>',
            "/$boardItem->id/tracks",
            [
                'class' => (!empty($trackerState)) ?
                    'red' :
                    'green',
                'data'  => ['spinner' => 'true',],
            ]
        ) .
        Html::tag('span', $trackerState, [
                'class' => 'badge',
            ]
        ),
        [
            'class' => "aria-toggle",
        ]
    );
} else {
    $tracksContent = "";
}*/
?>
<div class="panel-heading clearfix">
    <?= Html::tag(
        'h5',
        !empty($boardItem->panelItems['items']) ?
            Html::a(
                $boardItem->params['bundleParams']['title'],
                "javascript:void(0);"
            ) . $tracksContent  :
            Html::tag(
                "span",
                $boardItem->params['bundleParams']['title']
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
        <? if ( !empty($boardItem->params['bundleParams']['description']) ) : ?>
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