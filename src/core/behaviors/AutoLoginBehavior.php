<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace modular\core\behaviors;


use Yii;
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
                        $user = Yii::$app->user;
                        $logInIf = $user->isGuest || $user->identity->getId() !== $event->userId;
                        if ($logInIf) {
                            $user->logout(true);
                            /** @var object|IdentityInterface $identityModel */
                            $identityModel = Yii::createObject($user->identityClass);
                            $identity = $identityModel::findIdentity($event->userId);
                            if (!empty($identity)) {
                                $user->login($identity, $this->duration);
                            }
                        }
                    }
            ];
    }

}