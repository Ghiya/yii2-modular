<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace modular\core\behaviors;


use yii\base\Behavior;
use yii\web\IdentityInterface;

/**
 * Class AutoLoginBehavior
 * Поведение приложения для автоматичской авторизации пользователя по указанному параметру события.
 *
 * @package modular\core\behaviors
 */
class AutoLoginBehavior extends Behavior
{


    /**
     * Название события автоматической авторизации пользователя.
     */
    const EVENT_AUTO_LOGIN = 'modular.autoLoginEvent';


    /**
     * @var int|null
     */
    public $duration = 0;


    /**
     * {@inheritdoc}
     */
    public function events()
    {
        return
            [
                self::EVENT_AUTO_LOGIN =>
                    function (AutoLoginEvent $event) {
                        if (\Yii::$app->user->isGuest) {
                            /** @var object|IdentityInterface $identity */
                            $identity =
                                \Yii::createObject(
                                    \Yii::$app->user->identityClass
                                );
                            $user = $identity::findIdentity($event->userId);
                            if ( !empty($user) ) {
                                \Yii::$app->user->login($identity::findIdentity($event->userId), $this->duration);
                            }
                        }
                    }
            ];
    }

}