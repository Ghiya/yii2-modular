<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\core\tracker\behaviors;


use modular\core\Controller;
use modular\core\helpers\ArrayHelper;
use modular\core\tracker\events\Track;
use modular\core\tracker\TracksManager;
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
     * @var array заголовки пользовательских уведомлений
     */
    public $tracksTitles = [];


    /**
     * @var array идентификаторы событий пользовательских уведомлений
     */
    public $tracksEvents = [];


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
        foreach ($this->tracksEvents as $trackEvent) {
            $events[$trackEvent] = 'handleEvent';
        }
        return $events;
    }


    /**
     * Возвращает параметры и данные уведомления в зависимости от обрабатываемого события.
     *
     * @param Track $track событие уведомления
     */
    public function trackOnEvent(Track &$track)
    {
    }


    /**
     * Подготавливает уведомление веб-ресурса и вызывает событие обработки.
     *
     * @param Track|null $track
     */
    public function handleEvent(Track $track)
    {
        \Yii::debug("Handle web-resource track `" . $track->name . "`", __METHOD__);
        // configure track with module params ( default config )
        \Yii::configure($track, $this->owner->module->tracksConfig);
        // set custom track message
        $this->trackOnEvent($track);
        // add track title
        $this->addTitle($track);
        // add track owner's params
        $track
            ->model
            ->load(
                [
                    'resource_id' => $this->owner->module->bundleParams['module_id'],
                    'version'     => $this->owner->module->bundleParams['version']
                ]
            );
        // triggers handle event
        \Yii::$app->trigger(
            TracksManager::EVENT_HANDLE_TRACK,
            $track
        );
    }


    /**
     * Добавляет заголовок уведомления в зависимости от названия обрабатываемого события.
     *
     * @param Track $track
     */
    protected function addTitle(Track &$track)
    {
        $track->message =
            !empty($this->tracksTitles[$track->name]) ?
                (string)"<strong>" . $this->tracksTitles[$track->name] . "</strong>\r\n\r\n$track->message" : $track->message;
    }


    /**
     * @return Module|ResourceModule
     */
    protected function getModule()
    {
        return \Yii::$app->controller->module;
    }

}