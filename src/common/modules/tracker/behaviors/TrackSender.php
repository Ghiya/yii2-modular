<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace modular\common\modules\tracker\behaviors;


use modular\common\modules\tracker\components\Tracking;
use yii\base\Behavior;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;

/**
 * Class TrackSender
 *
 * @property-read Tracking $owner
 *
 * @package modular\common\modules\tracker\behaviors
 */
class TrackSender extends Behavior
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
     * @param TrackingEvent $tracking
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    protected function getRecipients(TrackingEvent $tracking)
    {
        foreach ($tracking->sendParams['observers'] as $observer) {
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