<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\core\tracker;


use modular\core\Application;
use modular\core\tracker\behaviors\Sender;
use modular\core\tracker\events\Track;
use yii\base\BootstrapInterface;
use yii\base\Component;


/**
 * Class TracksManager
 * Компонент управления треками уведомлений модулей веб-ресурсов.
 * Прикрепляет и конфигурирует поведение приложения для обработки и отправки уведомлений веб-ресурсов.
 *
 * > Note: Обязателен к загрузке на этапе сборки приложения `bootstrap`.
 *
 * @package modular\core\tracker
 */
class TracksManager extends Component implements BootstrapInterface
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
            function (Track $track) {
                $this->handleTrack($track);
                if ($track->forceSend) {
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
     * @param Track $track
     */
    protected function configSend(Track &$track)
    {
        foreach (array_keys($this->sendParams) as $param) {
            if (empty($track->sendParams[$param])) {
                $track->sendParams[$param] = $this->sendParams[$param];
            }
        }
        if ($track->devOnly) {
            $track->sendParams['observers'] =
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
     * @param Track $track
     */
    public function handleTrack(Track $track)
    {
        // создаём модель уведомления и добавляем её в очередь
        if (!empty($track->message)) {
            if ($track->keepTrack) {
                $track->getModel()->save(false);
            }
            $this->configSend($track);
            $this->getQueue()->enqueue($track);
        }
    }


    /**
     * Используя параметры отправки специфичные для каждого уведомления, отправляет их из очереди всем указанным
     * получателям. Если очередь пуста, то не отправляет ничего.
     */
    public function sendTracks()
    {
        while (!$this->getQueue()->isEmpty()) {
            /** @var Track $track */
            $track = $this->getQueue()->dequeue();
            \Yii::debug("Sending track `" . $track->getModel()->getMessageSubject() . "`", __METHOD__);
            if ($track->isSendEnable()) {
                foreach ($track->notifyBy as $senderId) {
                    $this->trigger(Sender::eventNameFor($senderId), $track);
                }
            }
        }
    }

}