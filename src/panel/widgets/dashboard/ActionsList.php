<?php
/**
 * @copyright Copyright (c) 2017. Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace modular\panel\widgets\dashboard;


use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\UnsetArrayValue;

/**
 * Class ActionsList
 * @package modular\panel\widgets\dashboard
 */
class ActionsList extends Widget
{


    public $viewPath = '@modular/panel/widgets/dashboard/views';


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
     * @var string $headerTitle
     */
    public $headerTitle = '';


    /**
     * @var array $wrapperOptions
     */
    public $wrapperOptions = [];


    /**
     * @var array $headerOptions
     */
    public $headerOptions = [];


    /**
     * @var array $listOptions
     */
    public $listOptions = [];


    /**
     * @var string $_itemsList
     */
    private $_itemsList = '';


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        // render items list
        $this->_itemsList = Html::tag(
            !empty($this->wrapperOptions['tag']) ?
                $this->wrapperOptions['tag'] : 'div',
            $this->_renderItemsListHeader() .
            $this->_renderItemsList($this->items) .
            $this->_renderItemsListFooter(),
            ArrayHelper::merge(
                $this->wrapperOptions,
                ['tag' => new UnsetArrayValue()]
            )
        );
    }


    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->_itemsList;
    }


    /**
     * Возвращает представление заголовка списка событий панели управления.
     * @return string
     */
    private function _renderItemsListHeader()
    {
        return Html::tag(
            !empty($this->wrapperOptions['tag']) ?
                $this->headerOptions['tag'] : 'h4',
            $this->headerTitle,
            ArrayHelper::merge(
                $this->headerOptions,
                ['tag' => new UnsetArrayValue()]
            )
        ) . '<div class="alert alert-warning error-text" style="display: none;"></div>';
    }


    /**
     * Возвращает представление списка элементов.
     *
     * @param array $items
     *
     * @return string
     */
    private function _renderItemsList($items = [])
    {
        // render column blocks
        foreach ($items as $item) {
            $this->_itemsList .= $this->_renderItem($item);
        }
        // render column blocks wrapper if required
        if (!empty($this->listOptions)) {
            $this->_itemsList = Html::tag(
                !empty($this->listOptions['tag']) ?
                    $this->listOptions['tag'] : 'div',
                $this->_itemsList,
                ArrayHelper::merge(
                    $this->listOptions,
                    ['tag' => new UnsetArrayValue()]
                )
            );
        }
        // return wrapped column
        return $this->_itemsList;
    }


    /**
     * Возвращает представление элемента списка.
     *
     * @param object $item
     *
     * @return string
     */
    private function _renderItem($item)
    {
        return !empty($this->itemView) ?
            $this->render(
                $this->viewPath . '/' . $this->itemView,
                [
                    'boardItem'  => $item,
                    'permission' => $this->permission,
                ]
            ) : '';
    }


    private function _renderItemsListFooter()
    {
        return Html::tag(
            'div',
            Html::a(
                'Посмотреть все события веб-ресурсов',
                ['/actions'],
                ['class' => 'revert red', 'data' => ['spinner' => 'true',],]
            ),
            ['class' => 'well text-center']
        );
    }

}