<?php
/**
 * @copyright Copyright (c) 2017. Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace panel\widgets\resource;


use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Class ItemPropertyDescription виджет расширенного описания аттрибута модели элемента списка панели администрирования
 * веб-ресурса через отображение `bootstrap popover`.
 *
 * @package panel\widgets\resource
 * @author  Ghiya Mikadze <ghiya@mikadze.me>
 */
class ItemPropertyDescription extends Widget
{


    /**
     * @var string $itemLabel заголовок аттрибута модели элемента
     */
    public $itemLabel = '';


    /**
     * @var string $itemTitle заголовок описания аттрибута модели элемента
     */
    public $title = '';


    /**
     * @var string $content описание аттрибута модели элемента
     */
    public $content = '';


    /**
     * @var string $button кнопка отображения расширенного описания
     */
    public $button = '<i class="fa fa-info-circle"></i>';


    /**
     * @var array $descriptionOptions массив параметров кнопки отображения описания
     */
    public $buttonOptions = [
        'class' => 'cp-item-property-popover pull-right',
    ];


    /**
     * @var array массив bootstrap popover параметров описания
     */
    public $popoverOptions =
        [
            'trigger'   => 'focus',
            'placement' => 'left',
        ];


    /**
     * @inheritdoc
     */
    public function run()
    {
        return
            Html::tag(
                'span',
                $this->button,
                ArrayHelper::merge(
                    $this->buttonOptions,
                    [
                        'role'     => 'button',
                        'tabindex' => 0,
                        'title'    => empty($this->title) ?
                            $this->itemLabel :
                            $this->title,
                        'data'     =>
                            ArrayHelper::merge(
                                $this->popoverOptions,
                                [
                                    'toggle'  => 'popover',
                                    'content' => $this->content
                                ]
                            ),
                    ]
                )
            ) .
            ' ' . $this->itemLabel;
    }


}