<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace modular\core\tracker\behaviors;


use modular\core\tracker\events\Track;
use modular\core\tracker\models\TrackData;
use yii\swiftmailer\Mailer;

/**
 * Class EmailSender
 * Поведение отправщика уведомлений через электронную почту.
 *
 * @package modular\core\tracker\behaviors
 */
class EmailSender extends Sender
{


    /**
     * @var string
     */
    protected $id = "email";


    /**
     * @var array
     */
    public $recipientValidator = 'email';


    /**
     * {@inheritdoc}
     *
     * @throws \yii\base\InvalidConfigException
     */
    protected function send(Track $track)
    {
        $emails = [];
        $subject = $track->getModel()->getMessageSubject();
        foreach ($this->getRecipients($track) as $recipient) {
            $emails[] = $this->getMailer()
                ->compose(
                    $this->getMailViewPath($track->priority),
                    [
                        'resource' => $track->sender->module->bundleParams['title'],
                        'subject'  => $subject,
                        'notice'   => $track->getModel()->messageParams(),
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
    protected function getMailer()
    {
        if (empty($this->_mailer)) {
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
            case TrackData::PRIORITY_WARNING :
                return '@resource/mail/tracker/warning-html';
                break;

            case TrackData::PRIORITY_NOTICE :
                return '@resource/mail/tracker/notice-html';
                break;

            default :
                return '@resource/mail/tracker/notice-html';
                break;
        }
    }

}