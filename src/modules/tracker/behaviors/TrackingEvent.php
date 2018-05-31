<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\modules\tracker\behaviors;


use modular\common\controllers\Controller;
use modular\common\helpers\ArrayHelper;
use modular\modules\tracker\models\Track;
use yii\base\Event;


/**
 * Class TrackingEvent
 * Событие контроллера с параметрами нового трека веб-ресурса.
 *
 * @property-read Controller $sender
 *
 * @package modular\modules\tracker\behaviors
 */
class TrackingEvent extends Event
{


    /**
     * @var array массив параметров уведомления
     */
    public $track = [];


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
     * @var Track
     */
    private $_model;


    /**
     * @return Track
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
        // init track first
        $this->track =
            ArrayHelper::merge(
                $this->getDefaultTrackParams(),
                $this->track
            );
        // add model
        $this->_model = new Track();
        $this->_model->load($this->track);
        $this->_model->allowed($this->allowedFor);
        $this->_model->validate();
        // add log info
        if ($this->track['priority'] == Track::PRIORITY_WARNING) {
            \Yii::warning($this->track['message'], __METHOD__);
        }
        else {
            \Yii::info($this->track['message'], __METHOD__);
        }
    }


    /**
     * @return array
     */
    protected function getDefaultTrackParams()
    {
        return
            [
                'priority' => Track::PRIORITY_NOTICE,
                'message'  => ''
            ];
    }


    /**
     * @return bool
     */
    public function isSendEnable()
    {
        return
            !empty($this->sendParams['notify']) && !empty($this->sendParams['observers']);
    }

}