<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\core;


/**
 * Class Module
 * Базовый класс модулей ресурсов и панелей администрирования.
 *
 * @package modular\core
 */
abstract class Module extends \yii\base\Module
{


    /**
     * @var string версия модуля
     */
    public $version = '';


    /**
     * @var string название модуля
     */
    public $title = '';


    /**
     * @var string описание модуля
     */
    public $description = '';


    /**
     * @var string символьный системный идентификатор пакета
     */
    public $cid = "";


    /**
     * @var bool триггер сервисного системного модуля
     */
    public $filterService = false;


    /**
     * @var array
     */
    public $package = [];


}