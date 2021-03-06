<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\panel\widgets;


use yii\data\BaseDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ListView;


/**
 * Class PanelListView
 *
 * @property BaseDataProvider $dataProvider
 *
 * @package modular\panel\widgets
 */
class PanelListView extends ListView
{


    /**
     * @var bool $useSelection
     */
    public $useSelection = false;


    /**
     * @inheritdoc
     */
    public $options = [
        'class' => 'panel-group cpanel-list text-center',
        'id'    => 'cpanel-list-accordion',
        'role'  => 'tablist',
        'tag'   => 'div',
    ];


    /**
     * @inheritdoc
     */
    public $itemOptions = [];


    /**
     * @inheritdoc
     */
    public $pager = [
        'hideOnSinglePage' => true,
        'maxButtonCount'   => 5,
        'options'          => ['class' => 'pagination',],
    ];


    /**
     * @inheritdoc
     */
    public $emptyText = '<i class="fa fa-minus"></i>';


    /**
     * @return string
     */
    protected function getSelectAllBlock()
    {
        return ($this->useSelection) ?
            Html::tag(
                'li',
                Html::tag(
                    'div',
                    "<p><input type='checkbox' name='selection' data-toggle='selection' class='cpanel-select-all'/></p>",
                    [
                        'class' => "col-xs-2 col-sm-1",
                    ]
                ),
                [
                    'class' => "list-group-item text-center bd-clear clearfix",
                ]
            ) :
            "";
    }


    /**
     * @inheritdoc
     */
    public function run()
    {
        // микширует значение параметров элемента списка с установленными в кнфигурации
        $this->itemOptions = ArrayHelper::merge(
            ['class' => 'cpanel-item', 'tag' => 'div',],
            $this->itemOptions
        );
        if (!empty($this->dataProvider->pagination)) {
            if ($this->dataProvider->totalCount > 1000) {
                $this->layout = "<div class='panel-manage'>\n{summary}\n"
                    . "{pager}</div>\n"
                    . $this->getSelectAllBlock()
                    . "\n{items}\n";
            } else {
                if ($this->dataProvider->pagination->pageSize > $this->dataProvider->totalCount) {
                    $this->layout = "<div class='panel-manage'>\n{summary}\n"
                        . "{pager}</div>\n"
                        . $this->getSelectAllBlock()
                        . "<div class='text-center'>\n\n</div>"
                        . "\n{items}\n";
                } else {
                    $this->layout = "<div class='panel-manage'>\n{summary}\n"
                        . "{pager}</div>\n"
                        . $this->getSelectAllBlock()
                        . "\n{items}\n"
                        . "<div class='panel-manage'>"
                        . Html::a("Показать все записи", Url::toRoute(
                            ArrayHelper::merge(
                                [strtok(\Yii::$app->request->url, '?'),],
                                ArrayHelper::merge(\Yii::$app->request->get(), ['page' => 0,])
                            )
                        ),
                            [
                                'class' => 'pagination',
                            ]
                        ) .
                        "\n</div>";
                }
            }
        } else {
            $this->layout = "<div class='text-center'>\n{summary}\n</div>"
                . "{pager}\n"
                . "<div class='text-center'>\n"
                . Html::a("Показать постранично", Url::toRoute(
                    ArrayHelper::merge(
                        [strtok(\Yii::$app->request->url, '?'),],
                        ArrayHelper::merge(\Yii::$app->request->get(), ['page' => 1,])
                    )
                ),
                    [
                        'class' => 'pagination',
                    ]
                )
                . "\n</div>"
                . $this->getSelectAllBlock()
                . "\n{items}\n";
        }
        return parent::run();
    }

}