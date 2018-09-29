<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\panel\widgets;


use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\Pjax;


/**
 * Class PanelItemModal виджет отображения списка элементов панелей администрирования модулей.
 *
 * @property string $shortDescriptionLink read-only HTML ссылка в списке элементов с коротким описанием
 * @property string $fullDescriptionLink  read-only HTML ссылка в списке элементов с полным описанием
 *
 * @package modular\panel\widgets
 */
class PanelItemModal extends Widget
{


    /**
     * @const string LIST_LINK_TYPE_MODAL
     */
    const LIST_LINK_TYPE_MODAL = 'modal';


    /**
     * @const string LIST_LINK_TYPE_COLLAPSE
     */
    const LIST_LINK_TYPE_COLLAPSE = 'collapse';


    /**
     * @const string LIST_LINK_TYPE_DIRECT
     */
    const LIST_LINK_TYPE_DIRECT = 'direct';


    /**
     * @var bool $itemViewSpinner отображать индикатор процесса при переходе к детальному просмотру
     */
    public $itemViewSpinner = false;


    /**
     * @var bool $isActiveItem если элемент активен
     */
    public $isActiveItem = false;


    /**
     * @var bool $isSafeDeleted если элемент был безопасно удалён
     */
    public $isSafeDeleted = false;


    /**
     * @var string $listLinkType тип перехода от списка к просмотру элемента
     *                           по-умолчанию : 'direct'
     */
    public $listLinkType = 'direct';


    /**
     * @var bool $useSelection
     */
    public $useSelection = false;


    /**
     * @var bool $removeAllowed если доступно удаление элементов списка
     */
    public $removeAllowed = false;


    /**
     * @var int $itemId идентификатор модели элемента
     */
    public $itemId = 0;


    /**
     * @var string $itemType тип элемента
     */
    public $itemType = '';


    /**
     * @var string $modalTitle заголовок модального окна
     */
    public $modalTitle = '';


    /**
     * @var string $firstRow
     */
    public $firstRow;


    /**
     * @var string $lastRow
     */
    public $lastRow;


    /**
     * @var string $shortDescription
     */
    public $shortDescription;


    /**
     * @var string $fullDescription
     */
    public $fullDescription;


    /**
     * @var bool $rowOnly если требуется вывести только ряд элемента
     */
    public $rowOnly = false;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        ob_start();
    }


    /**
     * @inheritdoc
     */
    public function run()
    {
        switch ($this->listLinkType) {

            case self::LIST_LINK_TYPE_DIRECT :
                echo $this->renderRowBlock(
                    [
                        'id'    => "cpanel-item-heading-id$this->itemId",
                        'role'  => 'tab',
                        'class' => ($this->isActiveItem) ?
                            'panel-heading cpanel-item-wrap clearfix active' :
                            'panel-heading cpanel-item-wrap clearfix',
                    ]
                );
                break;

            case self::LIST_LINK_TYPE_COLLAPSE :

                echo $this->renderRowBlock(
                    [
                        'id'    => "cpanel-item-heading-id$this->itemId",
                        'role'  => 'tab',
                        'class' => ($this->isActiveItem) ?
                            'panel-heading cpanel-item-wrap clearfix active' :
                            'panel-heading cpanel-item-wrap clearfix',
                    ]
                );
                ?>
                <div id="cpanel-item-collapsible-id<?= $this->itemId ?>"
                     class="panel-collapse collapse"
                     role="tablist"
                     aria-labelledby="cpanel-item-heading-id<?= $this->itemId ?>"
                     aria-expanded="false">
                    <div class="panel-body">
                        <? Pjax::begin(['enablePushState' => false, 'timeout' => 3000,]); ?>
                        <?= Html::beginForm(['view?id=' . $this->itemId,], 'get',
                            ['data' => ['pjax' => 1,],]) . Html::endForm() ?>
                        <!-- PJAX content here -->
                        <? Pjax::end(); ?>
                    </div>
                </div>
                <?
                break;

            default :
                break;
        }
        $content = ob_get_clean();
        return $content;
    }


    /**
     * Возвращает представление кнопки удаления элемента.
     *
     * @return string
     */
    protected function renderRemoveAction()
    {
        return Html::tag(
            'div',
            Html::a(
                '<i class="fa fa-times"></i>',
                [
                    "delete",
                ],
                [
                    'class' => "cp-edit-warning-action",
                    "data"  => [
                        "method"  => "post",
                        "params"  => Json::encode(
                            [
                                'id' => $this->itemId
                            ]
                        ),
                        "confirm" => "Удалить запись [ id$this->itemId ]?"
                    ],
                ]
            ),
            [
                'class' => 'col-xs-2 col-sm-1 col-md-1 col-lg-1',
            ]
        );
    }


    /**
     * Возвращает представление стартового блока ряда с элементом.
     *
     * @return string
     */
    protected function renderFirstCols()
    {
        return ($this->useSelection) ?
            Html::tag(
                'div',
                "<p>" . Html::checkbox('selected[]') . "</p>",
                [
                    'class' => 'col-xs-1 col-sm-1 col-md-1 col-lg-1',
                ]
            ) .
            Html::tag(
                'div',
                $this->firstRow,
                [
                    'class' => 'col-xs-1 col-sm-1 col-md-1 col-lg-1 text-center',
                ]
            ) :
            Html::tag(
                'div',
                $this->firstRow,
                [
                    'class' => 'col-xs-2 col-sm-2 col-md-2 col-lg-2 text-center',
                ]
            );
    }


    /**
     * Возвращает представление конечного блока ряда с элементом.
     * > Note: Не отображается в адаптивном варианте интерфейса.
     *
     * @return string
     */
    protected function renderLastCols()
    {
        return $this->removeAllowed ?
            Html::tag(
                'div',
                $this->lastRow,
                [
                    'class' => 'col-sm-3 col-md-3 col-lg-3 visible-sm visible-md visible-lg',
                ]
            ) .
            $this->renderRemoveAction() :
            Html::tag(
                'div',
                $this->lastRow,
                [
                    'class' => 'col-sm-4 col-md-4 col-lg-4 visible-sm visible-md visible-lg',
                ]
            );
    }


    /**
     * Возвращает представление центрального блока ряда с элементом.
     *
     * @return string
     */
    protected function renderCenterCols()
    {
        return (!empty($this->fullDescription)) ?
            Html::tag(
                'div',
                $this->getShortDescriptionLink(),
                [
                    'class' =>
                        $this->removeAllowed ?
                            'col-xs-8 col-sm-3 col-md-3 col-lg-3 text-center' :
                            'col-xs-10 col-sm-3 col-md-3 col-lg-3 text-center',
                ]
            ) .
            Html::tag(
                'div',
                $this->getFullDescriptionLink(),
                [
                    'class' => 'col-sm-3 col-md-3 col-lg-3 text-center visible-sm visible-md visible-lg',
                ]
            ) :
            Html::tag(
                'div',
                $this->getShortDescriptionLink(),
                [
                    'class' =>
                        $this->removeAllowed ?
                            'col-xs-8 col-sm-6 col-md-6 col-lg-6 text-center' :
                            'col-xs-10 col-sm-6 col-md-6 col-lg-6 text-center',
                ]
            );
    }


    /**
     * @param array $wrapperOptions
     *
     * @return string
     */
    protected function renderRowBlock($wrapperOptions = [])
    {
        return Html::tag(
            'div',
            $this->renderFirstCols() . $this->renderCenterCols() . $this->renderLastCols(),
            $wrapperOptions
        );
    }


    /**
     * Возвращает read-only HTML ссылку в списке элементов с коротким описанием.
     *
     * @return string
     */
    protected function getShortDescriptionLink()
    {
        switch ($this->listLinkType) {

            case self::LIST_LINK_TYPE_MODAL :
                return Html::a($this->shortDescription, '#view-modal', [
                    'encode'        => false,
                    'class'         => $this->isActiveItem ? 'text-center cpanel-item-link active' : 'text-center cpanel-item-link',
                    'aria-expanded' => 'false',
                    'data'          => [
                        'toggle' => 'modal',
                        'title'  => $this->modalTitle,
                        'url'    => Url::toRoute([$this->itemType . '/view?id=' . $this->itemId,]),
                        'pjax'   => 0,
                    ],
                ]);
                break;

            case self::LIST_LINK_TYPE_DIRECT :
                return Html::a(
                    $this->shortDescription,
                    [$this->itemType . '/view?id=' . $this->itemId,],
                    ArrayHelper::merge(
                        [
                            'encode' => false,
                            'class'  => 'cpanel-item-link',
                        ],
                        $this->itemViewSpinner ?
                            [
                                'data' => ['spinner' => 'true',],
                            ] :
                            []
                    )
                );
                break;

            case self::LIST_LINK_TYPE_COLLAPSE :
                return Html::a($this->shortDescription, '#cpanel-item-collapsible-id' . $this->itemId, [
                    'encode'        => false,
                    'class'         => 'cpanel-item-link text-center',
                    'aria-expanded' => 'false',
                    'aria-controls' => 'cpanel-item-collapsible-id' . $this->itemId,
                    'data'          => [
                        'toggle' => 'collapse',
                        'parent' => '#cpanel-list-accordion',
                        'pjax'   => 0,
                    ],
                ]);
                break;

            default :
                return '';
                break;
        }
    }


    /**
     * Возвращает read-only HTML ссылку в списке элементов с полным описанием.
     *
     * @return string
     */
    protected function getFullDescriptionLink()
    {
        switch ($this->listLinkType) {

            case self::LIST_LINK_TYPE_MODAL :
                return Html::a($this->fullDescription, '#view-modal', [
                    'encode'        => false,
                    'class'         => $this->isActiveItem ? 'text-center cpanel-item-link active' : 'text-center cpanel-item-link',
                    'aria-expanded' => 'false',
                    'data'          => [
                        'toggle' => 'modal',
                        'title'  => $this->modalTitle,
                        'url'    => Url::toRoute([$this->itemType . '/view?id=' . $this->itemId,]),
                        'pjax'   => 0,
                    ],
                ]);
                break;

            case self::LIST_LINK_TYPE_DIRECT :
                return Html::a(
                    $this->fullDescription,
                    [$this->itemType . '/view?id=' . $this->itemId,],
                    ArrayHelper::merge(
                        [
                            'encode' => false,
                            'class'  => 'cpanel-item-link',
                        ],
                        $this->itemViewSpinner ?
                            [
                                'data' => ['spinner' => 'true',],
                            ] :
                            []
                    )
                );
                break;

            case self::LIST_LINK_TYPE_COLLAPSE :
                return Html::a($this->fullDescription, '#cpanel-item-collapsible-id' . $this->itemId, [
                    'encode'        => false,
                    'class'         => 'cpanel-item-link font-book',
                    'aria-expanded' => 'false',
                    'data'          => [
                        'toggle'   => 'collapse',
                        'parent'   => '#cpanel-list-accordion',
                        'controls' => 'cpanel-item-collapsible-id' . $this->itemId,
                        'pjax'     => 0,
                    ],
                ]);
                break;
            default :
                return '';
                break;
        }
    }

}