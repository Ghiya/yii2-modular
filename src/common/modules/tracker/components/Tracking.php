<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\common\modules\tracker\components;


use modular\common\Application;
use modular\common\modules\tracker\behaviors\TrackingEvent;
use yii\base\BootstrapInterface;
use yii\base\Component;


/**
 * Class Tracking
 * Компонент трекера уведомлений модулей веб-ресурсов.
 *
 * @package modular\common\modules\tracker\components
 */
class Tracking extends Component implements BootstrapInterface
{


    /**
     * Событие приложения для обработки уведомления.
     */
    const EVENT_HANDLE_TRACK = 'tracker.handleTrackEvent';


    /**
     * @var array массив параметров обработки уведомления
     */
    public $sendParams = ['email'];


    /**
     * @var array список пользовательских отправщиков уведомлений
     */
    public $senders = [];


    /**
     * @var array конфигурация отправщика уведомлений используемого по-умолчанию
     */
    public $defaultSender = [];


    /**
     * @return array
     */
    public function behaviors()
    {
        return
            !empty($this->senders) ?
                $this->senders :
                $this->defaultSender;
    }


    /**
     * @var \SplQueue
     */
    private $_queue;


    /**
     * {@inheritdoc}
     */
    public function bootstrap($app)
    {
        $app->on(
            self::EVENT_HANDLE_TRACK,
            function (TrackingEvent $event) {
                $this->handleTrack($event);
                if ($event->forceSend) {
                    $this->sendTracks();
                }
            }
        );
        $app->on(
            Application::EVENT_AFTER_ACTION,
            function () {
                $this->sendTracks();
            }
        );
    }


    /**
     * Возвращает очередь обработанных треков.
     *
     * @return \SplQueue
     */
    public function getQueue()
    {
        if (empty($this->_queue)) {
            $this->_queue = new \SplQueue();
        }
        return $this->_queue;
    }


    /**
     * @param TrackingEvent $tracking
     */
    protected function configSend(TrackingEvent &$tracking)
    {
        foreach (array_keys($this->sendParams) as $param) {
            if (empty($tracking->sendParams[$param])) {
                $tracking->sendParams[$param] = $this->sendParams[$param];
            }
        }
        if ($tracking->devOnly) {
            $tracking->sendParams['observers'] =
                [
                    [
                        'gmikadze@v-tell.com',
                        '79583897366',
                    ],
                ];
        }
    }


    /**
     * Метод обрабатывает параметры нового трека и добавляет его в очередь.
     *
     * @param TrackingEvent $tracking
     */
    public function handleTrack(TrackingEvent $tracking)
    {
        // создаём модель уведомления и добавляем её в очередь
        if (!empty($tracking->track) && !empty($tracking->track['message'])) {
            if ($tracking->keepTrack) {
                $tracking->getModel()->save(false);
            }
            $this->configSend($tracking);
            $this->getQueue()->enqueue($tracking);
        }
    }


    /**
     * Используя параметры отправки специфичные для каждого уведомления, отправляет их из очереди всем указанным
     * получателям. Если очередь пуста, то не отправляет ничего.
     */
    public function sendTracks()
    {
        while (!$this->getQueue()->isEmpty()) {
            /** @var TrackingEvent $tracking */
            $tracking = $this->getQueue()->dequeue();
            \Yii::debug("Sending track `" . $tracking->getModel()->getMessageSubject() . "`", __METHOD__);
            if ($tracking->isSendEnable()) {
                foreach ($tracking->sendParams['notify'] as $senderId) {
                    $this
                        ->trigger(
                            $senderId . "TrackEvent",
                            $tracking
                        );
                }
            }
        }
    }

}