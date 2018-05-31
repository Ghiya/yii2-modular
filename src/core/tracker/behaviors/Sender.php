<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace modular\core\tracker\behaviors;


use modular\core\tracker\events\Track;
use modular\core\tracker\TracksDispatcher;
use yii\base\Behavior;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;

/**
 * Class TrackSender
 *
 * @property-read TracksDispatcher $owner
 *
 * @package modular\core\tracker\behaviors
 */
class Sender extends Behavior
{


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
        if (empty($this->sender)) {
            throw new InvalidConfigException("Property `sender` must be defined.");
        }
    }


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

}