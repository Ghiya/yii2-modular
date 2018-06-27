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
                    self::EVENT_CAUGHT_ERROR => 'createTrack',
                ]
            );
        foreach ($this->tracksEvents as $trackEvent) {
            $events[$trackEvent] = 'createTrack';
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
     * Создаёт новое событие уведомления веб-ресурса и вызывает его обработку.
     *
     * @param Track $track
     */
    public function createTrack(Track $track)
    {
        \Yii::debug("Handle web-resource track `" . $track->name . "`", __METHOD__);
        // configure track send params with module default if track defined are empty
        $this->configTrack($track);
        // set custom track message
        $this->trackOnEvent($track);
        // completes track model
        $this->completeModel($track);
        // triggers handle event
        \Yii::$app->trigger(
            TracksManager::EVENT_HANDLE_TRACK,
            $track
        );
    }


    /**
     * Конфигурирует уведомление перед обработкой.
     * Устанавливает кастомные параметры отправки уведомления, если они определены в событии.
     *
     * @param Track $track
     */
    protected function configTrack(Track &$track)
    {
        $config = $this->owner->module->tracksConfig;
        if (!empty($config)) {
            if (!empty($track->notifyBy)) {
                $config['sendParams']['notifyBy'] = $track->notifyBy;
            }
            if (!empty($track->observers)) {
                $config['sendParams']['observers'] = $track->observers;
            }
            \Yii::configure(
                $track,
                $config
            );
        }
    }


    /**
     * Добавляет заголовок уведомления в зависимости от названия обрабатываемого события.
     *
     * @param Track $track
     */
    protected function completeModel(Track &$track)
    {
        $track->model->load(
            [
                'message'     =>
                    !empty($this->tracksTitles[$track->name]) ?
                        (string)"<strong>" . $this->tracksTitles[$track->name] . "</strong>\r\n\r\n$track->message" : $track->message,
                'resource_id' => $this->owner->module->id,
                'version'     => $this->owner->module->version
            ]
        );
        if ($track->keepTrack) {
            $track->model->save(false);
        }
    }


    /**
     * @return Module|ResourceModule
     */
    protected function getModule()
    {
        return \Yii::$app->controller->module;
    }

}