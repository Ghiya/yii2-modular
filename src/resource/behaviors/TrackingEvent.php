<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace resource\behaviors;


use yii\base\Event;


/**
 * Class TrackingEvent событие с параметрами и данными нового уведомления веб-ресурса.
 *
 * @package resource\behaviors
 * @author  Ghiya Mikadze <ghiya@mikadze.me>
 */
class TrackingEvent extends Event
{


    /**
     * @var array $track массив данных и параметров уведомления
     */
    public $track = [];


    /**
     * @var string $message текст уведомления ( дополняет текст по-умолчанию )
     */
    public $message = '';


    /**
     * @var array $sendParams массив параметров отправки уведомлений
     */
    public $sendParams = ['email', 'message',];


    /**
     * @var bool $developersOnly отправлять уведомление только администратору
     */
    public $developersOnly = false;


    /**
     * @var bool $forceSend если требуется отправка уведомлений сразу после создания
     */
    public $forceSend = false;


    /**
     * @var array
     */
    public $messageParams;

}