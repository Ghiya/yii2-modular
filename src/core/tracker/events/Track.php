<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\core\tracker\events;


use modular\core\Controller;
use modular\core\tracker\models\TrackData;
use yii\base\Event;


/**
 * Class TrackingEvent
 * Абстрактный базовый класс события контроллера с параметрами нового уведомления веб-ресурса.
 *
 * @property-read Controller $sender
 * @property-read TrackData  $model
 * @property-read array      $observers
 * @property-read array      $notifyBy
 *
 * @package modular\core\tracker\behaviors
 */
abstract class Track extends Event
{


    /**
     * @var int приоритет уведомления
     */
    public $priority;


    /**
     * @var string текст уведомления ( дополняет текст по-умолчанию )
     */
    public $message = '';


    /**
     * @var array массив параметров отправки уведомлений
     */
    public $sendParams = [];


    /**
     * @var bool отправлять уведомление только администратору
     */
    public $devOnly = false;


    /**
     * @var bool если требуется отправка уведомлений сразу после создания
     */
    public $forceSend = false;


    /**
     * @var bool если требуется сохранить запись в БД
     */
    public $keepTrack = true;


    /**
     * @var array|null идентификаторы пользователей для которых разрешён просмотр трека
     */
    public $allowedFor;


    /**
     * @var array параметры для использования при рендеринге уведомления
     */
    public $messageParams = [];


    /**
     * @var TrackData
     */
    private $_model;


    /**
     * @return TrackData
     */
    public function getModel()
    {
        return $this->_model;
    }


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        // define model
        $this->_model = new TrackData();
        $this->_model->load(
            [
                'priority' => $this->priority,
                'message'  => $this->message,
            ]
        );
        $this->_model->allowed($this->allowedFor);
        $this->_model->validate();
        // add log info
        if ($this->priority == TrackData::PRIORITY_WARNING) {
            \Yii::warning($this->message, __METHOD__);
        }
        else {
            \Yii::info($this->message, __METHOD__);
        }
    }


    /**
     * Список данных получателей уведомлений.
     *
     * @return array
     */
    public function getObservers()
    {
        return
            isset($this->sendParams['observers']) ?
                (array)$this->sendParams['observers'] :
                [];
    }


    /**
     * Список используемых идентификаторов отправщиков уведомлений.
     *
     * @return array
     */
    public function getNotifyBy()
    {
        return
            !empty($this->sendParams['notifyBy']) ?
                (array)$this->sendParams['notifyBy'] :
                [];
    }


    /**
     * Если возможна отправка уведомления.
     *
     * @return bool
     */
    public function isSendEnable()
    {
        return !empty($this->notifyBy) && !empty($this->observers);
    }

}