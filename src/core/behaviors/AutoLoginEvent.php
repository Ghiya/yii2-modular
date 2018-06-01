<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\core\behaviors;


use yii\base\Event;

/**
 * Class AutoLoginEvent
 * @package modular\core\behaviors
 */
class AutoLoginEvent extends Event
{


    /**
     * @var int|string
     */
    public $userId;

}