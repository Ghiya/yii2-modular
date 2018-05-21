<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\resource\behaviors;


use modular\resource\components\Tracker;
use modular\resource\modules\Module;
use yii\base\Behavior;
use yii\helpers\ArrayHelper;
use yii\web\Controller;


/**
 * Class TrackingBehavior базовый класс поведения обработки уведомлений веб-ресурсов.
 *
 * @property Module $module   read-only
 * @property string $panelUrl read-only адрес URL административной панели веб-ресурса
 *
 * @package core\modules\core\behaviors
 * @author  Mikadze Ghiya <ghiya@mikadze.me>
 */
class TrackingBehavior extends Behavior
{


    /**
     * @const string событие обработки стандартной ошибки или выброшенного исключения контроллера
     */
    const EVENT_CAUGHT_ERROR = 'caughtErrorTrackingEvent';


    /**
     * @var Controller $owner
     */
    public $owner;


    /**
     * @var string $localization языковая локализация уведомлений
     */
    public $localization = 'ru-RU';


    /**
     * @var array $eventTitles заголовки текстов обрабатываемых событий
     */
    public $eventTitles = [];


    /**
     * @var array $eventTracks константы триггеров обрабатываемых событий
     */
    public $eventTracks = [];


    /**
     * @var array $developerIds
     */
    public $developerIds = [1, 4];


    /**
     * @inheritdoc
     *
     * Прикрепляет обработку уведомлений веб-ресурса.
     */
    public function events()
    {
        $events = ArrayHelper::merge(parent::events(), [
            Controller::EVENT_AFTER_ACTION => function ($event) {
                \Yii::$app->controller->module->get('tracker')->sendNotices();
            },
            self::EVENT_CAUGHT_ERROR       => 'handleEvent',
        ]);
        foreach ($this->eventTracks as $trackEvent) {
            $events[$trackEvent] = 'handleEvent';
        }
        return $events;
    }


    /**
     * Возвращает read-only адрес URL административной панели веб-ресурса.
     *
     * @return string
     */
    public function getPanelUrl()
    {
        return (defined("YII_DEBUG") && YII_DEBUG == true) ?
            'https://dev-services.v-tell.ru/' . $this->getModule()->params['bundleParams']['module_id'] :
            'https://services.v-tell.ru/' . $this->getModule()->params['bundleParams']['module_id'];
    }


    /**
     * Возвращает параметры и данные уведомления в зависимости от обрабатываемого события.
     *
     * @param TrackingEvent $event название события
     *
     * @return array вернёт пустой массив если для указанного события параметры не определены
     */
    public function trackOnEvent(TrackingEvent $event)
    {
        return [];
    }


    /**
     * Обрабатывает событие контроллера веб-ресурса.
     * Создаёт соответствующее уведомление веб-ресурса через системный компонент трекинга уведомлений.
     *
     * @param TrackingEvent|null $event
     */
    public function handleEvent(TrackingEvent $event = null)
    {
        // определяет данные уведомления
        if (empty($event->track)) {
            $track = $this->trackOnEvent($event);
        } else {
            $track = $event->track;
        }
        // устанавливает параметры и содержание уведомления
        $track = ArrayHelper::merge(
            $track,
            [
                'message' => (!empty($track['message'])) ?
                    $this->eventTitle($event->name) . $track['message'] . $event->message :
                    $this->eventTitle($event->name) . $event->message,
            ]
        );
        /** @var Tracker $tracker */
        $tracker = \Yii::$app->controller->module->get('tracker');
        // создаёт уведомление с получателями в зависимости от параметров события
        $tracker->handle(
            $track,
            $event->sendParams,
            ($event->developersOnly) ?
                [
                    [
                        'gmikadze@v-tell.com',
                        '79583897366',
                    ],
                ] :
                $tracker->notifyParams['observers'],
            true,
            ($event->developersOnly) ? $this->developerIds : null
        );
        // отправляет уведомление сразу если указано в параметрах события
        if ($event->forceSend) {
            $tracker->sendNotices();
        }
    }


    /**
     * Возвращает заголовок уведомления в зависимости от названия обрабатываемого события.
     *
     * @param string $eventName
     *
     * @return string
     */
    protected function eventTitle($eventName = '')
    {
        return (!empty($this->eventTitles[$eventName])) ? (string)"<strong>" . $this->eventTitles[$eventName] . "</strong>\r\n\r\n" : '';
    }


    /**
     * @return \yii\base\Module|Module
     */
    protected function getModule()
    {
        return \Yii::$app->controller->module;
    }

}