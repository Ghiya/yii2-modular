<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\core\tracker\behaviors;


use modular\core\Controller;
use modular\core\helpers\ArrayHelper;
use modular\core\tracker\events\Track;
use modular\core\tracker\TracksDispatcher;
use modular\resource\ResourceModule;
use yii\base\Behavior;
use yii\base\Module;


/**
 * Class TrackingBehavior базовый класс поведения обработки уведомлений веб-ресурсов.
 *
 * @property-read string $panelUrl URL административной панели веб-ресурса
 *
 * @package modular\core\tracker\behaviors
 * @author  Mikadze Ghiya <ghiya@mikadze.me>
 */
class Tracking extends Behavior
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
        $events =
            ArrayHelper::merge(
                parent::events(),
                [
                    self::EVENT_CAUGHT_ERROR => 'handleEvent',
                ]
            );
        foreach ($this->eventTracks as $trackEvent) {
            $events[$trackEvent] = 'handleEvent';
        }
        return $events;
    }


    /**
     * Возвращает параметры и данные уведомления в зависимости от обрабатываемого события.
     *
     * @param Track $event название события
     */
    public function trackOnEvent(Track &$event)
    {
    }


    /**
     * Обрабатывает уведомление веб-ресурса через модульный компонент трекера уведомлений.
     *
     * @param Track|null $event
     */
    public function handleEvent($event)
    {
        \Yii::debug("Handle track event `" . $event->name . "`", __METHOD__);
        // определяет данные уведомления
        $this->trackOnEvent($event);
        $event->track =
            ArrayHelper::merge(
                $event->track,
                [
                    'message'     => $this->eventTitle($event->name) . $event->track['message'],
                    'resource_id' => $event->sender->module->bundleParams['module_id'],
                    'version'     => $event->sender->module->bundleParams['version']
                ]
            );
        \Yii::$app->trigger(
            TracksDispatcher::EVENT_HANDLE_TRACK,
            $event
        );
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
     * @return Module|ResourceModule
     */
    protected function getModule()
    {
        return \Yii::$app->controller->module;
    }

}