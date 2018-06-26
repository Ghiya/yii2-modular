<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\core;

use modular\core\helpers\ArrayHelper;


/**
 * Class Module
 * Базовый класс модулей ресурсов и панелей администрирования.
 *
 * @property string $safeId         read-only идентификатор модуля с заменой символа `.`
 * @property string $strictId       read-only идентификатор модуля без указания папки расположения
 *
 * @package modular\core
 */
abstract class Module extends \yii\base\Module
{


    /**
     * @var string название модуля
     */
    public $title = '';


    /**
     * @var string описание модуля
     */
    public $description = '';


    /**
     * @var bool если модуль ресурса провайдера данных внешнего сервиса
     */
    public $isProvider = false;


    /**
     * @var bool если модуль системного компонента
     */
    public $isService = false;


    /**
     * @var bool если модуль веб-ресурса системы
     */
    public $isResource = false;


    /**
     * @var array
     */
    public $bundleParams = [];


}