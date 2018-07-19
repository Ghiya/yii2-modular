<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace modular\core;

use modular\core\helpers\ArrayHelper;
use modular\panel\PanelModule;
use modular\resource\ResourceModule;

/**
 * Class Controller
 * Абстрактный базовый класс контроллера модуля ресурса.
 *
 * @property PanelModule|ResourceModule $module
 *
 * @package modular\core
 */
abstract class Controller extends \yii\web\Controller
{


    /**
     * @var string|array
     */
    public $breadcrumb = 'Контроллер';


    /**
     * {@inheritdoc}
     */
    public function render($view, $params = [])
    {
        return parent::render($view, ArrayHelper::merge(['breadcrumb' => $this->breadcrumb], $params));
    }

}