<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace modular\core\tracker\behaviors;


use modular\core\tracker\events\Track;
use modular\core\tracker\TracksManager;
use yii\base\Behavior;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;

/**
 * Class Sender
 *
 * @property-read TracksManager $owner
 *
 * @package modular\core\tracker\behaviors
 */
abstract class Sender extends Behavior
{


    const DEFAULT_EVENT_POSTFIX = '.sendTrackEvent';


    /**
     * @var string|callable
     */
    public $recipientValidator;


    /**
     * @var string
     */
    public $sender = "";


    /**
     * @var string
     */
    protected $id = "";


    /**
     * @var array
     */
    private $_recipients = [];


    /**
     * {@inheritdoc}
     *
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty($this->id)) {
            throw new InvalidConfigException("Property `id` must be defined in class.");
        }
        if (empty($this->sender)) {
            throw new InvalidConfigException("Property `sender` must be defined.");
        }
    }


    /**
     * @return array
     */
    public function events()
    {
        return
            [
                self::eventNameFor($this->id) =>
                    function (Track $track) {
                        $this->send($track);
                    }
            ];
    }


    /**
     * @param Track $track
     *
     * @return mixed
     */
    abstract protected function send(Track $track);


    /**
     * @param Track $track
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    protected function getRecipients(Track $track)
    {
        foreach ($track->observers as $observer) {
            foreach ($observer as $contact) {
                $model = DynamicModel::validateData(['contact' => $contact,], [
                    ['contact', $this->recipientValidator],
                ]);
                if (!$model->hasErrors()) {
                    $this->_recipients[] = $contact;
                }
            }
        }
        return array_unique($this->_recipients);
    }


    /**
     * Возвращает название события отправки уведомления для указанного идентификатора отправщика.
     *
     * @param string $senderId
     *
     * @return string
     */
    public static function eventNameFor($senderId = "")
    {
        return "$senderId." . self::DEFAULT_EVENT_POSTFIX;
    }

}