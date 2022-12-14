<?php
/*
 * Copyright (c) 2016 - 2022 Ghiya Mikadze <g.mikadze@lakka.io>
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
     * @var boolean
     */
    public $activated;


    /**
     * @var string символьный системный идентификатор пакета
     */
    public $cid = "";


    /**
     * @var array прикреплённые URL
     */
    public $urls = [];


    /**
     * @var array
     */
    public $package = [];


}