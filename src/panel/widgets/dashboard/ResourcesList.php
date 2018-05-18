<?php
/**
 * @copyright Copyright (c) 2017. Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace panel\widgets\dashboard;


use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\UnsetArrayValue;

/**
 * Class ResourcesList
 * @package panel\widgets\dashboard
 */
class ResourcesList extends Widget
{


    public $viewPath = '@panel/widgets/dashboard/views';


    /**
     * @var string $permission
     */
    public $permission = '';


    /**
     * @var object[] array
     */
    public $items = [];


    /**
     * @var string $itemView
     */
    public $itemView = '';


    /**
     * @var array $itemOptions
     */
    public $itemOptions =
        [
            'class' => 'panel panel-default',
        ];


    /**
     * @var array $itemWrapperOptions
     */
    public $itemWrapperOptions = [];


    /**
     * @var string $columnTitle
     */
    public $columnTitle = '';


    /**
     * @var array $columnOptions
     */
    public $columnOptions = [];


    /**
     * @var array $columnHeaderOptions
     */
    public $columnHeaderOptions =
        [
            'class' => 'page-header green',
        ];


    /**
     * @var array $columnWrapperOptions
     */
    public $columnWrapperOptions = [];


    /**
     * @var string $_column
     */
    private $_column = '';


    /**
     * @var string $_columnHeader
     */
    private $_columnHeader = '';


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        // render items column
        if (!empty($this->items)) {
            $this->_columnHeader =
                !empty($this->columnTitle) ?
                    Html::tag(
                        !empty($this->columnOptions['tag']) ?
                            $this->columnHeaderOptions['tag'] : 'h4',
                        $this->columnTitle,
                        $this->_mergeOptions(
                            $this->columnHeaderOptions,
                            ['tag' => new UnsetArrayValue()]
                        )
                    ) :
                    "";
            $this->_column = $this->_renderColumn($this->items);
        }
    }


    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->_column;
    }


    private function _renderColumn($items = [])
    {
        // render column blocks
        foreach ($items as $item) {
            $this->_column .= $this->_renderBoardView($item);
        }
        // render column blocks wrapper if required
        if (!empty($this->columnWrapperOptions)) {
            $this->_column = Html::tag(
                !empty($this->columnWrapperOptions['tag']) ?
                    $this->columnWrapperOptions['tag'] : 'div',
                $this->_column,
                $this->_mergeOptions(
                    $this->columnWrapperOptions,
                    ['tag' => new UnsetArrayValue()]
                )
            );
        }
        // return wrapped column
        return Html::tag(
            !empty($this->columnOptions['tag']) ?
                $this->columnOptions['tag'] : 'div',
            $this->_columnHeader . $this->_column,
            $this->_mergeOptions(
                $this->columnOptions,
                ['tag' => new UnsetArrayValue()]
            )
        );
    }


    /**
     * Возвращает представление блока колонки элементов панели управления.
     *
     * @param object $item
     *
     * @return string
     */
    private function _renderBoardView($item)
    {
        return
            !empty($this->itemView) ?
                !empty($this->itemWrapperOptions) ?
                    Html::tag(
                        'div',
                        Html::tag(
                            'div',
                            $this->render(
                                $this->viewPath . '/' . $this->itemView,
                                [
                                    'boardItem'  => $item,
                                    'permission' => $this->permission,
                                ]
                            ),
                            $this->_mergeOptions(
                                [
                                    'class' => 'panel panel-default',
                                ],
                                $this->itemOptions
                            )
                        ),
                        $this->itemWrapperOptions
                    ) :
                    Html::tag(
                        'div',
                        $this->render(
                            $this->viewPath . '/' . $this->itemView,
                            [
                                'boardItem'  => $item,
                                'permission' => $this->permission,
                            ]
                        ),
                        $this->_mergeOptions(
                            [
                                'class' => 'panel panel-default',
                            ],
                            $this->itemOptions
                        )
                    ) :
                '';
    }


    /**
     * Сливает два массива с сохранением строковых значений первого.
     *
     * @param array $a
     * @param array $b
     *
     * @return array
     */
    private function _mergeOptions($a, $b)
    {
        $merged = [];
        foreach (ArrayHelper::merge($a, $b) as $k => $v) {
            if (is_string($v)) {
                $merged[$k] =
                    isset($b[$k]) ?
                        $a[$k] . ' ' . $b[$k] :
                        $a[$k];
            } elseif (is_array($v)) {
                $merged[$k] = $this->_mergeOptions($merged[$k], $v);
            } else {
                $merged[$k] = $v;
            }
        }
        return $merged;
    }
}