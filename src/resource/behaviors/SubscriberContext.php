<?php
/**
 * @copyright Copyright (c) 2017. Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\resource\behaviors;


use modular\resource\modules\_default\Module;
use modular\common\services\billing\Subscriber;
use modular\resource\Application;
use yii\base\Behavior;
use yii\helpers\ArrayHelper;


/**
 * Class SubscriberContext поведение идентификации абонента в системе управления
 *
 * @property Module $owner
 *
 * @package modular\resource\behaviors
 * @author  Ghiya Mikadze <ghiya@mikadze.me>
 */
class SubscriberContext extends Behavior
{


    /**
     * @const string EVENT_IDENTIFY
     */
    const EVENT_IDENTIFY = 'resource.identifySubscriberEvent';


    /**
     * @var string $headerAttribute название атрибута с идентификатором абонента в заголовке запроса
     */
    public $headerAttribute = 'X-Subscriber-Id';


    /**
     * @var string $bodyAttribute название аттрибута с идентификатором абонента в теле запроса
     */
    public $bodyAttribute = 'msisdn';


    /**
     * @inheritdoc
     */
    public function events()
    {
        return ArrayHelper::merge(
            parent::events(),
            [
                Application::EVENT_BEFORE_ACTION => 'identifySubscriber',
                self::EVENT_IDENTIFY             => 'identifySubscriber',
            ]
        );
    }


    /**
     * Определяет и возвращает идентификатор абонента, если он был указан.
     *
     * @return string
     */
    protected function getSubscriberId()
    {
        return ($this->idFromHeader() === null) ?
            $this->idFromBody() :
            $this->idFromHeader();
    }


    /**
     * Возвращает идентификатор абонента из заголовков запроса.
     *
     * @return string если идентификатор не указан, то вернёт пустую строку
     */
    protected function idFromHeader()
    {
        return \Yii::$app->request->getHeaders()->get($this->headerAttribute);
    }


    /**
     * Возвращает идентификатор абонента из тела запроса.
     *
     * @return string если идентификатор не указан, то вернёт пустую строку
     */
    protected function idFromBody()
    {
        return \Yii::$app->request->isGet ?
            \Yii::$app->request->get($this->bodyAttribute) :
            \Yii::$app->request->post($this->bodyAttribute);
    }


    /**
     * Производит идентификацию абонента при событии [[Application::EVENT_IDENTIFY]].
     * > Note: Функционал используется только в приложении веб-ресурсов [[\modular\resource\Application]].
     *
     * @param $event
     */
    public function identifySubscriber($event)
    {
        if (\Yii::$app->user->isGuest) {
            $subscriberIdentity = Subscriber::findIdentity(
                !empty($event) && ($event instanceof SubscriberContextEvent) ? $event->msisdn : $this->getSubscriberId()
            );
            if (!empty($subscriberIdentity)) {
                \Yii::$app->user->login($subscriberIdentity);
            }
        }
    }

}