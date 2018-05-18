<?php
/**
 * @copyright Copyright (c) 2017. Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace resource\behaviors;


use yii\base\Behavior;
use yii\base\Event;
use yii\base\Model;

/**
 * Class IdentityBehavior
 *
 * @package resource\behaviors
 */
class IdentityBehavior extends Behavior
{


    /**
     * @const string EVENT_IDENTIFY
     */
    const EVENT_IDENTIFY = 'identifySubscriber';


    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            Model::EVENT_AFTER_VALIDATE => 'identify',
            self::EVENT_IDENTIFY        => 'identify',
        ];
    }


    /**
     * Запускает идентификацию абонента в биллинге после успешной валидации соответствующей модели.
     *
     * @param Event $event
     */
    public function identify($event)
    {
        /** @var Model $model */
        $model = $event->sender;
        if ($this->_isIdAvailable($model)) {
            // идентифицирует абонента
            \Yii::$app->trigger(
                SubscriberContext::EVENT_IDENTIFY,
                new SubscriberContextEvent(
                    [
                        'msisdn' => $model->msisdn,
                    ]
                )
            );
        }
    }


    /**
     * Если возможна идентификация по данным модели.
     *
     * @param Model $model
     *
     * @return bool
     */
    private function _isIdAvailable($model)
    {
        return !$model->hasErrors('msisdn') &&
            $model->canGetProperty('msisdn') &&
            !empty($model->msisdn);
    }

}