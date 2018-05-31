<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace modular\modules\tracker\behaviors;


use modular\modules\tracker\models\Track;
use yii\swiftmailer\Mailer;

/**
 * Class EmailTrackSender
 *
 * @package modular\modules\tracker\behaviors
 */
class EmailTrackSender extends TrackSender
{


    const EVENT_SEND = 'emailTrackEvent';


    /**
     * @var array
     */
    public $recipientValidator = 'email';


    /**
     * @return array
     */
    public function events()
    {
        return
            [
                self::EVENT_SEND =>
                    function (TrackingEvent $tracking) {
                        $this->send($tracking);
                    }
            ];
    }


    /**
     * @param TrackingEvent $tracking
     *
     * @throws \yii\base\InvalidConfigException
     */
    protected function send(TrackingEvent $tracking)
    {
        $emails = [];
        $subject = $tracking->getModel()->getMessageSubject();
        foreach ($this->getRecipients($tracking) as $recipient) {
            $emails[] = $this->getMailer()
                ->compose(
                    $this->getMailViewPath($tracking->getModel()->priority),
                    [
                        'resource' => $tracking->sender->module->bundleParams['title'],
                        'subject'  => $subject,
                        'notice'   => $tracking->getModel()->messageParams(),
                    ]
                )
                ->setFrom($this->sender)
                ->setTo($recipient)
                ->setSubject($subject);
        }
        // отправляем уведомление на все адреса разработчиков
        $this->getMailer()->sendMultiple($emails);
    }


    /**
     * @var Mailer
     */
    private $_mailer;


    /**
     * @return Mailer
     */
    protected function getMailer() {
        if ( empty($this->_mailer) ) {
            $this->_mailer =
                new Mailer(
                    [
                        'useFileTransport' => false,
                        'htmlLayout'       => '@resource/mail/layouts/html',
                        'textLayout'       => '@resource/mail/layouts/text',
                    ]
                );
        }
        return $this->_mailer;
    }


    /**
     * @param int $priority
     *
     * @return string
     */
    protected function getMailViewPath($priority)
    {
        switch ($priority) {
            case Track::PRIORITY_WARNING :
                return '@resource/mail/tracker/warning-html';
                break;

            case Track::PRIORITY_NOTICE :
                return '@resource/mail/tracker/notice-html';
                break;

            default :
                return '@resource/mail/tracker/notice-html';
                break;
        }
    }

}