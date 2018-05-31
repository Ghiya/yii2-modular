<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace modular\core;

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


}