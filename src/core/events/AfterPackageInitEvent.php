<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace modular\core\events;


use modular\core\models\PackageInit;
use modular\panel\PanelModule;
use modular\resource\ResourceModule;
use yii\base\Event;

/**
 * Class AfterPackageInitEvent
 *
 * @package modular\core\events
 */
class AfterPackageInitEvent extends Event
{


    /**
     * @var array
     */
    public $config = [];


    /**
     * @var PanelModule|ResourceModule
     */
    public $module;


    /**
     * @var PackageInit
     */
    public $packageInit;
}