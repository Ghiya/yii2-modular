<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\core\tracker\events;


use modular\core\Controller;
use modular\core\helpers\ArrayHelper;
use modular\core\tracker\models\TrackData;
use modular\panel\models\UserRole;
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
     * @deprecated использовать свойство [[$allowedRoles]]
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
     * @deprecated использовать свойство [[$allowedRoles]]
     * @var array|null идентификаторы пользователей для которых разрешён просмотр трека
     */
    public $allowedFor;

    /**
     * @var array|null
     */
    public $allowedRoles;

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
    public function getModel(): TrackData
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
        // @todo удалить в дальнейшем
        $this->_model->usersAllowed($this->allowedFor);
        if ($this->devOnly) {
            $this->allowedRoles = ArrayHelper::merge(
                !empty($this->allowedRoles) ? $this->allowedRoles : [],
                [UserRole::RL_ROOT, UserRole::RL_ADMINISTRATOR, UserRole::RL_ENGINEER]
            );
        }
        if (!empty($this->allowedRoles)) {
            foreach (UserRole::findAllWith($this->allowedRoles) as $role) {
                $this->_model->allowedFor = $role->user_id;
            }
        }
        $this->_model->validate();
        // add log info
        if ($this->priority == TrackData::PRIORITY_WARNING) {
            \Yii::warning($this->message, __METHOD__);
        } else {
            \Yii::info($this->message, __METHOD__);
        }
    }


    /**
     * Список данных получателей уведомлений.
     *
     * @return array
     */
    public function getObservers(): array
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
    public function getNotifyBy(): array
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
    public function isSendEnable(): bool
    {
        return !empty($this->notifyBy) && !empty($this->observers);
    }

}