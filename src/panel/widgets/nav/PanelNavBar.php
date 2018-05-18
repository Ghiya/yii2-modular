<?php
/**
 * @copyright Copyright (c) 2017. Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace modular\panel\widgets\nav;


use yii\bootstrap\NavBar;
use yii\helpers\Html;

/**
 * Class PanelNavBar виджет навигационной панели административного приложения системы.
 *
 * @package modular\panel\widgets\nav
 * @author  Ghiya Mikadze <ghiya@mikadze.me>
 */
class PanelNavBar extends NavBar
{


    /**
     * @var bool $renderInnerContainer
     */
    public $renderInnerContainer = true;


    /**
     * @var array $innerContainerOptions
     */
    public $innerContainerOptions =
        [
            'class' => 'container-fluid',
        ];


    /**
     * @inheritdoc
     */
    protected function renderToggleButton()
    {
        $bar = Html::tag("i", "", ["class" => "fa fa-bars"]);
        $screenReader = "<span class=\"sr-only\">{$this->screenReaderToggleText}</span>";
        return Html::button("{$screenReader}\n{$bar}", [
            'class'       => 'navbar-toggle',
            'data-toggle' => 'collapse',
            'data-target' => "#{$this->containerOptions['id']}",
        ]);
    }

}